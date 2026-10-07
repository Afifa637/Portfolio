<?php

/**
 * Site & SEO settings.
 *
 * The form renders itself from site_settings: each row carries its own label,
 * input type and hint, so adding an editable string is an INSERT rather than a
 * code change.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_admin();
require_once __DIR__ . '/_helpers.php';

$groupLabels = [
    'identity' => ['Identity', 'Your name, role and how visitors reach you.'],
    'hero'     => ['Hero', 'The first thing anyone reads.'],
    'about'    => ['About', 'Your story. A blank line starts a new paragraph.'],
    'contact'  => ['Contact', 'The heading and lead above the contact form.'],
    'seo'      => ['Search & social', 'What search engines and link previews show.'],
    'status'   => ['System status', 'The live status panel in the contact section and footer.'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_require_post_csrf();

    $rows    = Database::all('SELECT setting_key, input_type FROM site_settings');
    $saved   = 0;
    $errors  = [];

    foreach ($rows as $row) {
        $key  = (string) $row['setting_key'];
        $type = (string) $row['input_type'];

        /*
         * Only touch settings the request actually carried.
         *
         * Writing every row on every POST means any field missing from the
         * submission is silently blanked — a partial form, a disabled input, or
         * a malformed request wipes content that took real effort to write.
         * Checkboxes are the one exception: an unchecked box sends nothing, so
         * absence is the value.
         */
        if ($type !== 'bool' && !array_key_exists($key, $_POST) && empty($_FILES[$key . '_upload']['name'])) {
            continue;
        }

        if ($type === 'image') {
            // An upload replaces the typed path.
            try {
                $uploaded = admin_store_upload($key . '_upload');

                if ($uploaded !== null) {
                    Database::execute('UPDATE site_settings SET value = ? WHERE setting_key = ?', [$uploaded, $key]);
                    $saved++;
                    continue;
                }
            } catch (RuntimeException $e) {
                $errors[] = $e->getMessage();
                continue;
            }
        }

        $value = $type === 'bool'
            ? (isset($_POST[$key]) ? '1' : '0')
            : trim((string) ($_POST[$key] ?? ''));

        if (Database::execute('UPDATE site_settings SET value = ? WHERE setting_key = ?', [$value, $key])) {
            $saved++;
        }
    }

    admin_redirect(
        'settings.php',
        $errors !== []
            ? implode(' ', $errors)
            : "Saved. {$saved} setting" . ($saved === 1 ? '' : 's') . ' updated — the site is live with your changes.',
        $errors === []
    );
}

$settings = Database::all('SELECT * FROM site_settings ORDER BY group_key, order_no, id');
$grouped  = [];

foreach ($settings as $setting) {
    $grouped[(string) $setting['group_key']][] = $setting;
}

admin_head('Site & SEO');

?>
<div class="admin-head">
    <p class="admin-blurb">
        Every string here appears on the public site. Changes take effect as soon as you save.
    </p>
</div>

<?php if ($settings === []): ?>
    <p class="admin-empty">
        No settings found. Run <code>php database/migrate.php</code> to import them from your profile.
    </p>
<?php else: ?>

<form class="admin-form" method="post" action="settings.php" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <?php foreach ($grouped as $group => $items): ?>
        <?php [$title, $blurb] = $groupLabels[$group] ?? [ucfirst($group), '']; ?>

        <section class="card" style="display:grid;gap:var(--sp-4)">
            <div>
                <h2 style="font-size:var(--fs-lg);border:0;padding:0"><?= e($title) ?></h2>
                <?php if ($blurb !== ''): ?>
                    <p class="field-hint" style="margin-top:4px"><?= e($blurb) ?></p>
                <?php endif; ?>
            </div>

            <div class="admin-form-grid">
                <?php foreach ($items as $item): ?>
                    <?php
                    admin_field([
                        'name'  => (string) $item['setting_key'],
                        'label' => (string) $item['label'],
                        'type'  => (string) $item['input_type'],
                        'hint'  => (string) ($item['hint'] ?? ''),
                        'rows'  => 5,
                    ], $item['value']);
                    ?>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>

    <div class="admin-form-actions">
        <button class="btn btn-primary" type="submit"><?= icon('check', 16) ?> Save all settings</button>
        <a class="btn btn-ghost" href="../index.php" target="_blank" rel="noopener">
            <?= icon('external', 16) ?> Preview site
        </a>
    </div>
</form>

<?php endif; ?>

<?php admin_foot(); ?>
