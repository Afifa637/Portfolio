<?php

/**
 * Generate the Open Graph card and the Apple touch icon.
 *
 *   php -d extension=gd tools/make-images.php
 *
 * The OG card is what appears when the site is shared on LinkedIn, WhatsApp,
 * Slack or X. Without one, those unfurls fall back to a cropped avatar or
 * nothing at all — which is a poor first impression for a link a recruiter
 * opens. Generating it here keeps it in step with config/profile.php.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit("CLI only.\n");
}

if (!extension_loaded('gd')) {
    exit("GD is not loaded. Run with:  php -d extension=gd tools/make-images.php\n");
}

require_once __DIR__ . '/../includes/bootstrap.php';

$identity = Content::get('identity', []);

/** Allocate a colour from a #rrggbb string. */
function hex(GdImage $im, string $hex, int $alpha = 0): int
{
    [$r, $g, $b] = sscanf(ltrim($hex, '#'), '%2x%2x%2x') ?: [0, 0, 0];

    return imagecolorallocatealpha($im, (int) $r, (int) $g, (int) $b, $alpha);
}

/* ------------------------------------------------------------- OG card ---- */

const W = 1200;
const H = 630;

$im = imagecreatetruecolor(W, H);
imageantialias($im, true);

$bg     = hex($im, '#0b0d14');
$accent = hex($im, '#ffb454');
$violet = hex($im, '#bb9af7');
$text   = hex($im, '#e4e9f5');
$dim    = hex($im, '#a3adc6');
$mute   = hex($im, '#6b768f');
$border = hex($im, '#222941');

imagefilledrectangle($im, 0, 0, W, H, $bg);

/**
 * Corner glow.
 *
 * Blended per pixel rather than stacked as translucent ellipses: overlapping
 * ellipses accumulate alpha toward opaque at the centre, which produced a solid
 * blob over the name instead of a wash.
 */
$glow = static function (
    GdImage $im,
    int $cx,
    int $cy,
    float $radius,
    array $rgb,
    float $peak
): void {
    $x0 = max(0, (int) ($cx - $radius));
    $x1 = min(imagesx($im) - 1, (int) ($cx + $radius));
    $y0 = max(0, (int) ($cy - $radius));
    $y1 = min(imagesy($im) - 1, (int) ($cy + $radius));

    for ($x = $x0; $x <= $x1; $x++) {
        for ($y = $y0; $y <= $y1; $y++) {
            $d = sqrt(($x - $cx) ** 2 + ($y - $cy) ** 2) / $radius;

            if ($d >= 1.0) {
                continue;
            }

            // Smooth falloff, strongest at the centre.
            $t = (1 - $d) ** 2 * $peak;

            $base = imagecolorat($im, $x, $y);
            $br = ($base >> 16) & 0xFF;
            $bg2 = ($base >> 8) & 0xFF;
            $bb = $base & 0xFF;

            imagesetpixel($im, $x, $y, imagecolorallocate(
                $im,
                (int) round($br + ($rgb[0] - $br) * $t),
                (int) round($bg2 + ($rgb[1] - $bg2) * $t),
                (int) round($bb + ($rgb[2] - $bb) * $t)
            ));
        }
    }
};

$glow($im, 90, 30, 540, [255, 180, 84], 0.30);
$glow($im, 1180, 640, 460, [187, 154, 247], 0.22);

// Dot grid texture.
for ($x = 0; $x < W; $x += 26) {
    for ($y = 0; $y < H; $y += 26) {
        imagesetpixel($im, $x, $y, $border);
    }
}

$font = __DIR__ . '/../assets/fonts/og.ttf';
$hasTtf = function_exists('imagettftext') && is_file($font);

if ($hasTtf) {
    imagettftext($im, 24, 0, 80, 132, $mute,   $font, strtoupper($identity['location']));
    imagettftext($im, 88, 0, 76, 266, $text,   $font, $identity['first_name']);
    imagettftext($im, 88, 0, 76, 372, $accent, $font, $identity['last_name']);
    imagettftext($im, 32, 0, 80, 452, $dim,    $font, $identity['title']);
    imagettftext($im, 22, 0, 80, 556, $mute,   $font, 'github.com/' . $identity['github_user']);
} else {
    // No TrueType support: fall back to the bitmap font, which is small but
    // still produces a legible, on-brand card rather than nothing.
    $line = static function (string $s, int $x, int $y, int $colour, int $scale) use ($im): void {
        for ($dx = 0; $dx < $scale; $dx++) {
            for ($dy = 0; $dy < $scale; $dy++) {
                imagestring($im, 5, $x + $dx, $y + $dy, $s, $colour);
            }
        }
    };

    $line(strtoupper($identity['location']), 80, 110, $mute, 1);
    $line($identity['first_name'] . ' ' . $identity['last_name'], 80, 210, $text, 2);
    $line($identity['title'], 80, 300, $accent, 2);
    $line('github.com/' . $identity['github_user'], 80, 520, $mute, 1);
}

// Accent rule under the name block.
imagefilledrectangle($im, 80, 496, 260, 500, $accent);
imagefilledrectangle($im, 266, 496, 342, 500, $violet);

imagepng($im, __DIR__ . '/../assets/images/og-cover.png', 6);
imagedestroy($im);

echo "  ✓ assets/images/og-cover.png  (1200×630)\n";

/* -------------------------------------------------- Apple touch icon ---- */

$size = 180;
$icon = imagecreatetruecolor($size, $size);
imageantialias($icon, true);

imagefilledrectangle($icon, 0, 0, $size, $size, hex($icon, '#0b0d14'));

// Diagonal accent wash.
for ($i = 0; $i < $size; $i++) {
    $t = $i / $size;
    $c = imagecolorallocatealpha(
        $icon,
        (int) (255 - 68 * $t),
        (int) (180 - 26 * $t),
        (int) (84 + 163 * $t),
        96
    );
    imageline($icon, 0, $i, $i, 0, $c);
}

$initials = mb_substr($identity['first_name'], 0, 1) . mb_substr($identity['last_name'], 0, 1);
$white    = hex($icon, '#ffffff');

if ($hasTtf) {
    $box = imagettfbbox(74, 0, $font, $initials);
    $tw  = $box[2] - $box[0];
    imagettftext($icon, 74, 0, (int) (($size - $tw) / 2), 122, $white, $font, $initials);
} else {
    for ($dx = 0; $dx < 3; $dx++) {
        for ($dy = 0; $dy < 3; $dy++) {
            imagestring($icon, 5, 68 + $dx, 78 + $dy, $initials, $white);
        }
    }
}

imagepng($icon, __DIR__ . '/../assets/apple-touch-icon.png', 6);
imagedestroy($icon);

echo "  ✓ assets/apple-touch-icon.png  (180×180)\n";

if (!$hasTtf) {
    echo "\n  Note: no TrueType font found at assets/fonts/og.ttf, so the bitmap\n";
    echo "        fallback was used. Drop any .ttf there and re-run for sharper text.\n";
}

echo "\n";
