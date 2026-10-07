<?php

/**
 * Admin shared helpers: layout chrome, form controls, and media handling.
 */

declare(strict_types=1);

/* ------------------------------------------------------------ navigation -- */

/** @return array<string, array{label: string, icon: string, group: string}> */
function admin_nav(): array
{
    return [
        'index.php'      => ['label' => 'Dashboard',   'icon' => 'layout',     'group' => 'Overview'],
        'messages.php'   => ['label' => 'Messages',    'icon' => 'mail',       'group' => 'Overview'],
        'email.php'      => ['label' => 'Email setup', 'icon' => 'send',       'group' => 'Overview'],

        'settings.php'   => ['label' => 'Site & SEO',  'icon' => 'settings',   'group' => 'Content'],
        'projects.php'   => ['label' => 'Projects',    'icon' => 'layers',     'group' => 'Content'],
        'resource.php?r=skill_groups' => ['label' => 'Skill groups', 'icon' => 'code',       'group' => 'Content'],
        'resource.php?r=skills'       => ['label' => 'Skills',       'icon' => 'code',       'group' => 'Content'],
        'resource.php?r=education'    => ['label' => 'Education',    'icon' => 'graduation', 'group' => 'Content'],
        'resource.php?r=experience'   => ['label' => 'Experience',   'icon' => 'briefcase',  'group' => 'Content'],
        'resource.php?r=activities'   => ['label' => 'Activities',   'icon' => 'award',      'group' => 'Content'],
        'resource.php?r=services'     => ['label' => 'Services',     'icon' => 'zap',        'group' => 'Content'],
        'resource.php?r=about_facts'  => ['label' => 'About facts',  'icon' => 'book',       'group' => 'Content'],
        'resource.php?r=principles'   => ['label' => 'How I think',  'icon' => 'route',      'group' => 'Content'],
        'resource.php?r=blueprint_stages' => ['label' => 'Request blueprint', 'icon' => 'server', 'group' => 'Content'],
        'resource.php?r=journey'      => ['label' => 'Journey',      'icon' => 'calendar',   'group' => 'Content'],

        'resource.php?r=home_roles'       => ['label' => 'Hero roles',  'icon' => 'terminal', 'group' => 'Details'],
        'resource.php?r=home_socials'     => ['label' => 'Social links', 'icon' => 'github',  'group' => 'Details'],
        'resource.php?r=contact_channels' => ['label' => 'Contact info', 'icon' => 'phone',   'group' => 'Details'],
        'resource.php?r=contact_purposes' => ['label' => 'Enquiry types', 'icon' => 'check',  'group' => 'Details'],
        'resource.php?r=project_categories' => ['label' => 'Categories', 'icon' => 'layers',  'group' => 'Details'],

        'media.php'      => ['label' => 'Media',       'icon' => 'copy',       'group' => 'Library'],
    ];
}

/* ---------------------------------------------------------------- layout -- */

function admin_head(string $title): void
{
    $unread = Database::hasTable('contact_messages')
        ? (int) (Database::first('SELECT COUNT(*) AS n FROM contact_messages WHERE is_read = 0')['n'] ?? 0)
        : 0;

    $current = basename($_SERVER['SCRIPT_NAME'] ?? '');
    $query   = $_GET['r'] ?? '';
    $activeKey = $query !== '' ? "{$current}?r={$query}" : $current;

    ?><!doctype html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title) ?> — Portfolio admin</title>
    <link rel="icon" href="../assets/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
    <?php /* Paths are prefixed with ../ because asset() returns a path relative
             to the project root, while these pages are served from /admin/. */ ?>
    <link rel="stylesheet" href="<?= e(asset('admin/css/admin.css')) ?>">
    <script>
        (function () {
            try {
                var t = localStorage.getItem('portfolio-theme');
                document.documentElement.dataset.theme = (t === 'light' || t === 'dark') ? t : 'dark';
            } catch (e) { document.documentElement.dataset.theme = 'dark'; }
        })();
    </script>
    <?= import_map() ?>
</head>
<body class="admin">
<a class="skip-link" href="#admin-main">Skip to content</a>

<aside class="admin-sidebar" id="admin-sidebar">
    <div class="admin-brand">
        <span class="mark" aria-hidden="true">AS</span>
        <span>
            <strong>Portfolio</strong>
            <small>Content manager</small>
        </span>
    </div>

    <nav class="admin-nav" aria-label="Admin sections">
        <?php
        $lastGroup = null;

        foreach (admin_nav() as $href => $item):
            if ($item['group'] !== $lastGroup):
                $lastGroup = $item['group'];
                ?>
                <p class="admin-nav-group"><?= e($lastGroup) ?></p>
            <?php endif; ?>

            <a href="<?= e($href) ?>" class="admin-nav-link<?= $href === $activeKey ? ' is-active' : '' ?>">
                <?= icon($item['icon'], 16) ?>
                <span><?= e($item['label']) ?></span>
                <?php if ($href === 'messages.php' && $unread > 0): ?>
                    <span class="admin-badge"><?= $unread ?></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="admin-sidebar-foot">
        <a class="admin-nav-link" href="../index.php" target="_blank" rel="noopener">
            <?= icon('external', 16) ?> <span>View site</span>
        </a>
        <a class="admin-nav-link" href="change_password.php">
            <?= icon('settings', 16) ?> <span>Password</span>
        </a>
        <a class="admin-nav-link" href="logout.php">
            <?= icon('arrow-right', 16) ?> <span>Sign out</span>
        </a>
    </div>
</aside>

<div class="admin-shell">
    <header class="admin-topbar">
        <button class="btn btn-icon admin-menu-toggle" id="admin-menu-toggle" type="button" aria-label="Toggle menu">
            <?= icon('menu', 20) ?>
        </button>
        <h1><?= e($title) ?></h1>
        <div class="admin-topbar-actions">
            <button class="btn btn-icon theme-btn" type="button" data-action="theme" aria-label="Switch theme">
                <span class="sun"><?= icon('sun', 17) ?></span>
                <span class="moon"><?= icon('moon', 17) ?></span>
            </button>
        </div>
    </header>

    <main class="admin-main" id="admin-main">
        <?php admin_flash(); ?>
    <?php
}

function admin_foot(): void
{
    ?>
    </main>
</div>

<div class="admin-overlay" id="admin-overlay" hidden></div>
<script type="module">import { initTheme } from '@/core/theme.js'; initTheme();</script>
<script src="<?= e(asset('admin/js/admin.js')) ?>" defer></script>
</body>
</html>
    <?php
}

function admin_flash(): void
{
    $success = flash('admin_success');
    $error   = flash('admin_error');

    if ($success !== null) {
        echo '<p class="alert alert-ok" role="status">' . icon('check', 18) . '<span>' . e($success) . '</span></p>';
    }

    if ($error !== null) {
        echo '<p class="alert alert-err" role="alert">' . icon('x', 18) . '<span>' . e($error) . '</span></p>';
    }
}

/* ------------------------------------------------------------ form parts -- */

/**
 * Render one labelled form control.
 *
 * @param array{name:string,label:string,type?:string,hint?:string,required?:bool,options?:array,rows?:int,placeholder?:string} $field
 */
function admin_field(array $field, mixed $value = null): void
{
    $name     = $field['name'];
    $type     = $field['type'] ?? 'text';
    $id       = 'f_' . preg_replace('/[^a-z0-9_]/i', '_', $name);
    $required = !empty($field['required']);
    $value    = $value ?? ($field['default'] ?? '');

    echo '<div class="field">';
    echo '<label for="' . e($id) . '">' . e($field['label']);

    if ($required) {
        echo ' <span class="req" aria-hidden="true">*</span>';
    }

    echo '</label>';

    if (!empty($field['hint'])) {
        echo '<p class="field-hint">' . e($field['hint']) . '</p>';
    }

    switch ($type) {
        case 'textarea':
            printf(
                '<textarea id="%s" name="%s" rows="%d"%s placeholder="%s">%s</textarea>',
                e($id),
                e($name),
                (int) ($field['rows'] ?? 5),
                $required ? ' required' : '',
                e($field['placeholder'] ?? ''),
                e((string) $value)
            );
            break;

        case 'select':
            printf('<select id="%s" name="%s"%s>', e($id), e($name), $required ? ' required' : '');

            if (empty($required)) {
                echo '<option value="">—</option>';
            }

            foreach (($field['options'] ?? []) as $optValue => $optLabel) {
                printf(
                    '<option value="%s"%s>%s</option>',
                    e((string) $optValue),
                    (string) $optValue === (string) $value ? ' selected' : '',
                    e((string) $optLabel)
                );
            }

            echo '</select>';
            break;

        case 'bool':
            printf(
                '<label class="switch"><input type="checkbox" id="%s" name="%s" value="1"%s><span class="switch-track"></span><span class="switch-label">%s</span></label>',
                e($id),
                e($name),
                !empty($value) ? ' checked' : '',
                e($field['on_label'] ?? 'Enabled')
            );
            break;

        case 'image':
            echo '<div class="image-field">';
            printf(
                '<div class="image-preview"%s>%s</div>',
                $value ? '' : ' data-empty="true"',
                $value
                    ? '<img src="' . e(asset((string) $value)) . '" alt="">'
                    : '<span>No image</span>'
            );
            echo '<div class="image-field-controls">';
            printf(
                '<input type="text" id="%s" name="%s" value="%s" placeholder="assets/images/example.jpg" data-image-path>',
                e($id),
                e($name),
                e((string) $value)
            );
            printf(
                '<input type="file" name="%s_upload" accept="image/png,image/jpeg,image/webp" data-image-upload>',
                e($name)
            );
            echo '<p class="field-hint">Upload a PNG, JPEG or WebP — it is resized and converted automatically.</p>';
            echo '</div></div>';
            break;

        case 'list':
            // One item per line, stored as a comma-separated column.
            printf(
                '<textarea id="%s" name="%s" rows="%d" placeholder="%s" class="mono-input">%s</textarea>',
                e($id),
                e($name),
                (int) ($field['rows'] ?? 4),
                e($field['placeholder'] ?? 'One per line'),
                e(implode("\n", array_filter(array_map('trim', explode(',', (string) $value)))))
            );
            break;

        default:
            printf(
                '<input type="%s" id="%s" name="%s" value="%s"%s placeholder="%s"%s>',
                e($type),
                e($id),
                e($name),
                e((string) $value),
                $required ? ' required' : '',
                e($field['placeholder'] ?? ''),
                isset($field['maxlength']) ? ' maxlength="' . (int) $field['maxlength'] . '"' : ''
            );
    }

    echo '</div>';
}

/* ----------------------------------------------------------------- media -- */

/**
 * Store an uploaded image, resizing and converting it the same way
 * tools/optimize-images.php does.
 *
 * Returns the web-relative path, or null when nothing was uploaded.
 * Throws RuntimeException with a human-readable reason on a bad upload.
 */
function admin_store_upload(string $inputName, string $targetDir = 'assets/images'): ?string
{
    if (empty($_FILES[$inputName]) || ($_FILES[$inputName]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$inputName];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException(match ($file['error']) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'That image is larger than the server allows.',
            UPLOAD_ERR_PARTIAL                        => 'The upload was interrupted. Please try again.',
            default                                   => 'The upload failed. Please try again.',
        });
    }

    if ($file['size'] > 8 * 1024 * 1024) {
        throw new RuntimeException('Please keep images under 8 MB.');
    }

    // Trust the file's actual contents, never the client-supplied MIME type.
    $info = @getimagesize($file['tmp_name']);

    if ($info === false) {
        throw new RuntimeException('That file is not a readable image.');
    }

    $allowed = [IMAGETYPE_PNG => 'png', IMAGETYPE_JPEG => 'jpg', IMAGETYPE_WEBP => 'webp'];

    if (!isset($allowed[$info[2]])) {
        throw new RuntimeException('Only PNG, JPEG and WebP images are accepted.');
    }

    $base = pathinfo((string) $file['name'], PATHINFO_FILENAME);
    $slug = trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower($base)), '-') ?: 'image';

    $dir = APP_ROOT . '/' . trim($targetDir, '/');

    if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException('The image directory is not writable.');
    }

    // Never overwrite an existing file.
    $name = $slug;
    $n = 1;

    while (is_file("{$dir}/{$name}.jpg") || is_file("{$dir}/{$name}.webp")) {
        $name = $slug . '-' . (++$n);
    }

    // With GD we can resize and emit WebP + JPEG, matching the build pipeline.
    if (extension_loaded('gd')) {
        $source = match ($info[2]) {
            IMAGETYPE_PNG  => @imagecreatefrompng($file['tmp_name']),
            IMAGETYPE_JPEG => @imagecreatefromjpeg($file['tmp_name']),
            IMAGETYPE_WEBP => @imagecreatefromwebp($file['tmp_name']),
            default        => false,
        };

        if ($source instanceof GdImage) {
            $maxW = 1280;
            $w = imagesx($source);
            $h = imagesy($source);

            if ($w > $maxW) {
                $target = imagecreatetruecolor($maxW, (int) round($h * ($maxW / $w)));
                imagealphablending($target, false);
                imagesavealpha($target, true);
                imagecopyresampled($target, $source, 0, 0, 0, 0, imagesx($target), imagesy($target), $w, $h);
                imagedestroy($source);
                $source = $target;
            }

            imagepalettetotruecolor($source);
            imagewebp($source, "{$dir}/{$name}.webp", 82);

            // JPEG has no alpha; composite onto the page background.
            $flat = imagecreatetruecolor(imagesx($source), imagesy($source));
            imagefilledrectangle($flat, 0, 0, imagesx($source), imagesy($source), imagecolorallocate($flat, 11, 13, 20));
            imagealphablending($flat, true);
            imagecopy($flat, $source, 0, 0, 0, 0, imagesx($source), imagesy($source));
            imagejpeg($flat, "{$dir}/{$name}.jpg", 80);

            imagedestroy($flat);
            imagedestroy($source);

            return trim($targetDir, '/') . '/' . $name . '.jpg';
        }
    }

    // No GD: store the original, still safely re-encoded by extension.
    $extension = $allowed[$info[2]];
    $path = "{$dir}/{$name}.{$extension}";

    if (!move_uploaded_file($file['tmp_name'], $path)) {
        throw new RuntimeException('Could not save the uploaded image.');
    }

    return trim($targetDir, '/') . '/' . $name . '.' . $extension;
}

/** Guard every mutating request. */
function admin_require_post_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        admin_redirect($_SERVER['REQUEST_URI'] ?? 'index.php', 'Your session expired. Please try again.', false);
    }
}
