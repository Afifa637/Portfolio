<?php

/**
 * Build-time image pipeline.
 *
 *   php -d extension=gd tools/optimize-images.php
 *
 * Screenshots were committed as full-resolution PNGs — one was 2.5 MB for a
 * card that renders 640 px wide. PNG is the wrong container for photographic
 * content: lossless compression of a screenshot costs an order of magnitude
 * more than a high-quality lossy encode nobody can tell apart.
 *
 * For each source image this writes:
 *
 *   <name>.webp   the primary, served to every modern browser
 *   <name>.jpg    the fallback, for anything that cannot decode WebP
 *
 * Filenames are normalised to lowercase kebab-case, so "Heritage Explorer.png"
 * stops becoming "Heritage%20Explorer.png" in markup and logs.
 *
 * Sources are read from assets/images/original/ when present, so repeated runs
 * never re-encode an already-compressed file and compound the loss.
 *
 * The production site never calls GD: every output is committed.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit("CLI only.\n");
}

if (!extension_loaded('gd')) {
    exit("GD is not loaded. Run with:  php -d extension=gd tools/optimize-images.php\n");
}

const IMAGES   = __DIR__ . '/../assets/images';
const ORIGINAL = __DIR__ . '/../assets/images/original';

const MAX_W        = 1280;  // widest render: the case-study modal hero
const WEBP_QUALITY = 82;
const JPEG_QUALITY = 80;

/** Portraits are displayed smaller than screenshots. */
const NARROW = ['profile1' => 900];

function slug(string $filename): string
{
    $name = pathinfo($filename, PATHINFO_FILENAME);
    $name = preg_replace('/([a-z0-9])([A-Z])/', '$1-$2', $name) ?? $name;
    $name = strtolower($name);
    $name = preg_replace('/[^a-z0-9]+/', '-', $name) ?? $name;

    return trim($name, '-');
}

function loadImage(string $file): ?GdImage
{
    $image = match (@exif_imagetype($file) ?: 0) {
        IMAGETYPE_PNG  => @imagecreatefrompng($file),
        IMAGETYPE_JPEG => @imagecreatefromjpeg($file),
        IMAGETYPE_WEBP => @imagecreatefromwebp($file),
        IMAGETYPE_GIF  => @imagecreatefromgif($file),
        default        => false,
    };

    return $image instanceof GdImage ? $image : null;
}

function resize(GdImage $src, int $targetW): GdImage
{
    $w = imagesx($src);
    $h = imagesy($src);

    if ($w <= $targetW) {
        return $src;
    }

    $dst = imagecreatetruecolor($targetW, (int) round($h * ($targetW / $w)));

    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
    imagecopyresampled($dst, $src, 0, 0, 0, 0, imagesx($dst), imagesy($dst), $w, $h);

    return $dst;
}

/** JPEG has no alpha; composite onto the page background instead of black. */
function flatten(GdImage $src): GdImage
{
    $flat = imagecreatetruecolor(imagesx($src), imagesy($src));

    imagefilledrectangle($flat, 0, 0, imagesx($src), imagesy($src), imagecolorallocate($flat, 11, 13, 20));
    imagealphablending($flat, true);
    imagecopy($flat, $src, 0, 0, 0, 0, imagesx($src), imagesy($src));

    return $flat;
}

@mkdir(ORIGINAL, 0775, true);

/* Prefer pristine sources; fall back to whatever is in assets/images. */
$sources = glob(ORIGINAL . '/*.{png,jpg,jpeg}', GLOB_BRACE) ?: [];

if ($sources === []) {
    $sources = glob(IMAGES . '/*.{png,jpg,jpeg}', GLOB_BRACE) ?: [];

    // First run: archive the untouched files before rewriting anything.
    foreach ($sources as $file) {
        $archive = ORIGINAL . '/' . basename($file);

        if (!is_file($archive)) {
            copy($file, $archive);
        }
    }
}

printf("\n  %-26s %10s %10s %10s   %s\n", 'SOURCE', 'BEFORE', 'WEBP', 'JPEG', 'OUTPUT NAME');
echo '  ' . str_repeat('-', 78) . "\n";

$before = 0;
$afterWebp = 0;
$afterJpeg = 0;
$map = [];

foreach ($sources as $file) {
    $name  = slug(basename($file));
    $image = loadImage($file);

    if (!$image) {
        printf("  %-26s %10s   (unreadable, skipped)\n", basename($file), '—');
        continue;
    }

    $size = filesize($file);
    $resized = resize($image, NARROW[$name] ?? MAX_W);

    imagepalettetotruecolor($resized);

    $webpPath = IMAGES . '/' . $name . '.webp';
    $jpegPath = IMAGES . '/' . $name . '.jpg';

    imagewebp($resized, $webpPath, WEBP_QUALITY);

    $flat = flatten($resized);
    imagejpeg($flat, $jpegPath, JPEG_QUALITY);
    imagedestroy($flat);

    clearstatcache();

    $before     += $size;
    $afterWebp  += filesize($webpPath);
    $afterJpeg  += filesize($jpegPath);

    $map[basename($file)] = $name;

    printf(
        "  %-26s %9.0fK %9.0fK %9.0fK   %s.{webp,jpg}\n",
        substr(basename($file), 0, 26),
        $size / 1024,
        filesize($webpPath) / 1024,
        filesize($jpegPath) / 1024,
        $name
    );

    if ($resized !== $image) {
        imagedestroy($resized);
    }
    imagedestroy($image);
}

/* Remove the oversized originals from the served directory — the archive in
   assets/images/original/ keeps them, and nothing references them any more. */
$removed = 0;

foreach (glob(IMAGES . '/*.{png,jpg,jpeg}', GLOB_BRACE) ?: [] as $file) {
    $base = basename($file);

    // Keep the files this run just produced, and anything generated elsewhere.
    if (in_array($base, array_map(static fn($n) => $n . '.jpg', $map), true)) {
        continue;
    }

    if (in_array($base, ['og-cover.png'], true)) {
        continue;
    }

    if (isset($map[$base]) && @unlink($file)) {
        $removed++;
    }
}

echo '  ' . str_repeat('-', 78) . "\n";
printf(
    "  %-26s %9.0fK %9.0fK %9.0fK\n",
    'TOTAL',
    $before / 1024,
    $afterWebp / 1024,
    $afterJpeg / 1024
);

printf(
    "\n  Delivered weight: %.0f KB (WebP) — %.1f%% smaller than %.0f KB\n",
    $afterWebp / 1024,
    $before > 0 ? (1 - $afterWebp / $before) * 100 : 0,
    $before / 1024
);

printf("  Superseded originals removed from assets/images/: %d\n", $removed);
echo "  Pristine sources archived in assets/images/original/\n\n";

if ($map !== []) {
    echo "  Path mapping for config/profile.php:\n";

    foreach ($map as $from => $to) {
        printf("    assets/images/%-28s → assets/images/%s.jpg\n", $from, $to);
    }

    echo "\n";
}
