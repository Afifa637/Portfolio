<?php

/**
 * Media library.
 *
 * Upload a screenshot once here, then reference its path from any project.
 * Uploads go through the same resize-and-convert pipeline as the build tool,
 * so an 8 MB phone screenshot becomes a web-sized WebP plus a JPEG fallback.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_admin();
require_once __DIR__ . '/_helpers.php';

const MEDIA_DIR = 'assets/images';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_require_post_csrf();

    if (($_POST['action'] ?? '') === 'delete') {
        $name = basename((string) ($_POST['file'] ?? ''));

        $path = MEDIA_DIR . '/' . $name;

        // Refuse to delete an image anything still points at. Checking only
        // projects meant the portrait and the social share card — which live in
        // site_settings — could be deleted out from under the live site.
        $usedBy = null;

        $project = Database::first('SELECT title FROM projects WHERE image = ? LIMIT 1', [$path]);

        if ($project) {
            $usedBy = 'the project "' . $project['title'] . '"';
        } elseif (Database::hasTable('site_settings')) {
            $setting = Database::first(
                'SELECT label FROM site_settings WHERE value = ? LIMIT 1',
                [$path]
            );

            if ($setting) {
                $usedBy = 'the "' . $setting['label'] . '" setting';
            }
        }

        if ($usedBy !== null) {
            admin_redirect('media.php', 'That image is still used by ' . $usedBy . '.', false);
        }

        $absolute = APP_ROOT . '/' . $path;
        $removed  = 0;

        // Remove the JPEG and its WebP sibling together.
        foreach ([$absolute, preg_replace('/\.(jpe?g|png)$/i', '.webp', $absolute)] as $candidate) {
            if ($candidate && is_file($candidate) && @unlink($candidate)) {
                $removed++;
            }
        }

        admin_redirect('media.php', $removed > 0 ? 'Image deleted.' : 'Could not delete that image.', $removed > 0);
    }

    try {
        $stored = admin_store_upload('file', MEDIA_DIR);

        admin_redirect(
            'media.php',
            $stored !== null ? 'Uploaded as ' . $stored : 'Choose a file first.',
            $stored !== null
        );
    } catch (RuntimeException $e) {
        admin_redirect('media.php', $e->getMessage(), false);
    }
}

// List the delivered files only; each .jpg has a matching .webp beside it.
$files = array_values(array_filter(
    glob(APP_ROOT . '/' . MEDIA_DIR . '/*.{jpg,jpeg,png}', GLOB_BRACE) ?: [],
    static fn(string $f): bool => !str_contains(str_replace(DIRECTORY_SEPARATOR, '/', $f), '/original/')
));

usort($files, static fn(string $a, string $b): int => filemtime($b) <=> filemtime($a));

$used = array_column(Database::all("SELECT image FROM projects WHERE image <> ''"), 'image');

admin_head('Media');

?>
<div class="admin-head">
    <p class="admin-blurb">
        Images are resized to 1280px and saved as WebP with a JPEG fallback — the site serves
        whichever the visitor's browser supports.
    </p>
</div>

<form class="card admin-form" method="post" action="media.php" enctype="multipart/form-data" style="margin-bottom:var(--sp-6)">
    <?= csrf_field() ?>
    <div class="field">
        <label for="f_file">Upload an image</label>
        <p class="field-hint">PNG, JPEG or WebP, up to 8 MB.</p>
        <input type="file" id="f_file" name="file" accept="image/png,image/jpeg,image/webp" required>
    </div>
    <div class="admin-form-actions">
        <button class="btn btn-primary" type="submit"><?= icon('check', 16) ?> Upload</button>
    </div>
</form>

<?php if ($files === []): ?>
    <p class="admin-empty">No images yet.</p>
<?php else: ?>
    <div class="media-grid">
        <?php foreach ($files as $file): ?>
            <?php
            $name = basename($file);
            $path = MEDIA_DIR . '/' . $name;
            $isUsed = in_array($path, $used, true);
            ?>
            <figure class="media-item">
                <img src="../<?= e(asset($path)) ?>" alt="<?= e($name) ?>" loading="lazy">
                <figcaption class="media-meta">
                    <code><?= e($path) ?></code>
                    <span class="size">
                        <?= number_format(filesize($file) / 1024) ?> KB
                        <?= $isUsed ? ' · in use' : '' ?>
                    </span>
                    <div style="display:flex;gap:6px;flex-wrap:wrap">
                        <button class="btn btn-sm btn-ghost" type="button" data-copy="<?= e($path) ?>">
                            <span data-copy-label>Copy path</span>
                        </button>
                        <?php if (!$isUsed): ?>
                            <button class="btn btn-sm btn-danger" type="submit" form="rm-<?= e(md5($name)) ?>"
                                    data-confirm="Delete <?= e($name) ?>?">Delete</button>
                        <?php endif; ?>
                    </div>
                </figcaption>
            </figure>

            <?php if (!$isUsed): ?>
                <form method="post" action="media.php" id="rm-<?= e(md5($name)) ?>" class="visually-hidden">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="file" value="<?= e($name) ?>">
                </form>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php admin_foot(); ?>
