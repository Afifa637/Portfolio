<?php

/**
 * Generic CRUD screen.
 *
 * Renders a full list / create / edit / delete / reorder interface for any
 * table described in admin/_resources.php.
 *
 *   resource.php?r=services              list
 *   resource.php?r=services&edit=4       edit form
 *   resource.php?r=services&new=1        create form
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_admin();

require_once __DIR__ . '/_helpers.php';
require_once __DIR__ . '/_resources.php';

$resources = admin_resources();
$key       = (string) ($_GET['r'] ?? '');

if (!isset($resources[$key])) {
    http_response_code(404);
    admin_head('Unknown section');
    echo '<p class="alert alert-err">' . icon('x', 18) . '<span>No such section.</span></p>';
    admin_foot();
    exit;
}

$resource = $resources[$key];
$table    = $key;
$fields   = $resource['fields'];
$self     = 'resource.php?r=' . urlencode($key);

/* ------------------------------------------------------------- actions ---- */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_require_post_csrf();

    $action = (string) ($_POST['action'] ?? 'save');

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);

        /*
         * Some rows are referenced by other tables. Deleting a skill group
         * would leave its skills pointing at an id that no longer exists, and
         * deleting a category would leave its projects unfilterable. The hook
         * repairs those references before the row goes.
         */
        if (isset($resource['on_delete']) && is_callable($resource['on_delete'])) {
            $blocked = $resource['on_delete']($id);

            if (is_string($blocked)) {
                admin_redirect($self, $blocked, false);
            }
        }

        $deleted = Database::execute("DELETE FROM `{$table}` WHERE id = ?", [$id]);

        admin_redirect(
            $self,
            $deleted ? ucfirst($resource['singular']) . ' deleted.' : 'Could not delete that record.',
            $deleted
        );
    }

    if ($action === 'reorder') {
        $order = array_map('intval', (array) ($_POST['order'] ?? []));

        foreach ($order as $position => $id) {
            Database::execute("UPDATE `{$table}` SET order_no = ? WHERE id = ?", [$position, $id]);
        }

        admin_redirect($self, 'Order saved.');
    }

    // Create or update.
    $id   = (int) ($_POST['id'] ?? 0);
    $data = [];

    foreach ($fields as $field) {
        $name = $field['name'];
        $type = $field['type'] ?? 'text';

        $value = match ($type) {
            'bool'  => isset($_POST[$name]) ? 1 : 0,
            // Stored as a comma-separated column, edited one per line.
            'list'  => implode(', ', array_filter(array_map('trim', preg_split('/\r?\n/', (string) ($_POST[$name] ?? '')) ?: []))),
            default => trim((string) ($_POST[$name] ?? '')),
        };

        if ($type === 'select' && $value === '') {
            $value = null;
        }

        $data[$name] = $value;
    }

    // An image field may carry an upload, which supersedes the typed path.
    foreach ($fields as $field) {
        if (($field['type'] ?? '') !== 'image') {
            continue;
        }

        try {
            $uploaded = admin_store_upload($field['name'] . '_upload');

            if ($uploaded !== null) {
                $data[$field['name']] = $uploaded;
            }
        } catch (RuntimeException $e) {
            admin_redirect($self . ($id ? '&edit=' . $id : '&new=1'), $e->getMessage(), false);
        }
    }

    // Validate required fields before touching the database.
    $missing = [];

    foreach ($fields as $field) {
        if (!empty($field['required']) && ($data[$field['name']] ?? '') === '') {
            $missing[] = $field['label'];
        }
    }

    if ($missing !== []) {
        admin_redirect(
            $self . ($id ? '&edit=' . $id : '&new=1'),
            'Please fill in: ' . implode(', ', $missing) . '.',
            false
        );
    }

    if (isset($resource['before_save']) && is_callable($resource['before_save'])) {
        $data = $resource['before_save']($data, $id);
    }

    $columns = array_keys($data);

    if ($id > 0) {
        $assignments = implode(', ', array_map(static fn(string $c): string => "`{$c}` = ?", $columns));
        $ok = Database::execute(
            "UPDATE `{$table}` SET {$assignments} WHERE id = ?",
            [...array_values($data), $id]
        );
        $message = $ok ? ucfirst($resource['singular']) . ' updated.' : 'Could not save those changes.';
    } else {
        // New rows go to the end of the list.
        if (in_array('order_no', array_column(Database::all("SHOW COLUMNS FROM `{$table}`"), 'Field'), true)) {
            $data['order_no'] = (int) (Database::first("SELECT COALESCE(MAX(order_no), -1) + 1 AS n FROM `{$table}`")['n'] ?? 0);
            $columns = array_keys($data);
        }

        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $columnList   = '`' . implode('`, `', $columns) . '`';

        $ok = Database::execute(
            "INSERT INTO `{$table}` ({$columnList}) VALUES ({$placeholders})",
            array_values($data)
        );
        $message = $ok ? ucfirst($resource['singular']) . ' created.' : 'Could not create that record.';
    }

    admin_redirect($self, $message, $ok);
}

/* --------------------------------------------------------------- render ---- */

$editId  = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$creating = isset($_GET['new']);
$editing = $editId > 0
    ? Database::first("SELECT * FROM `{$table}` WHERE id = ?", [$editId])
    : null;

$rows = Database::all("SELECT * FROM `{$table}` ORDER BY " . $resource['order']);

admin_head($resource['title']);

?>
<div class="admin-head">
    <div>
        <p class="admin-blurb"><?= e($resource['blurb']) ?></p>
    </div>
    <?php if (!$creating && !$editing): ?>
        <a class="btn btn-primary" href="<?= e($self) ?>&new=1">
            <?= icon('check', 16) ?> Add <?= e($resource['singular']) ?>
        </a>
    <?php endif; ?>
</div>

<?php if ($creating || $editing): ?>

    <form class="card admin-form" method="post" action="<?= e($self) ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <?php if ($editing): ?>
            <input type="hidden" name="id" value="<?= (int) $editing['id'] ?>">
        <?php endif; ?>

        <h2><?= $editing ? 'Edit' : 'New' ?> <?= e($resource['singular']) ?></h2>

        <div class="admin-form-grid">
            <?php foreach ($fields as $field): ?>
                <?php admin_field($field, $editing[$field['name']] ?? ($field['default'] ?? '')); ?>
            <?php endforeach; ?>
        </div>

        <div class="admin-form-actions">
            <button class="btn btn-primary" type="submit"><?= icon('check', 16) ?> Save</button>
            <a class="btn btn-ghost" href="<?= e($self) ?>">Cancel</a>
        </div>
    </form>

<?php else: ?>

    <?php if ($rows === []): ?>
        <p class="admin-empty">
            Nothing here yet.
            <a href="<?= e($self) ?>&new=1">Add the first <?= e($resource['singular']) ?></a>.
        </p>
    <?php else: ?>
        <form method="post" action="<?= e($self) ?>" id="reorder-form">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="reorder">

            <div class="admin-table-wrap">
                <table class="admin-table" data-sortable>
                    <thead>
                        <tr>
                            <th class="col-handle"><span class="visually-hidden">Reorder</span></th>
                            <?php foreach ($resource['columns'] as $label): ?>
                                <th><?= e($label) ?></th>
                            <?php endforeach; ?>
                            <th class="col-actions"><span class="visually-hidden">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <tr draggable="true" data-id="<?= (int) $row['id'] ?>">
                                <td class="col-handle">
                                    <span class="drag-handle" aria-hidden="true">⠿</span>
                                    <input type="hidden" name="order[]" value="<?= (int) $row['id'] ?>">
                                </td>
                                <?php foreach (array_keys($resource['columns']) as $column): ?>
                                    <td><?= e(str_excerpt((string) ($row[$column] ?? ''), 70)) ?></td>
                                <?php endforeach; ?>
                                <td class="col-actions">
                                    <a class="btn btn-sm btn-ghost" href="<?= e($self) ?>&edit=<?= (int) $row['id'] ?>">Edit</a>
                                    <button class="btn btn-sm btn-danger" type="submit"
                                            form="delete-<?= (int) $row['id'] ?>"
                                            data-confirm="Delete this <?= e($resource['singular']) ?>? This cannot be undone.">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="admin-form-actions">
                <button class="btn btn-ghost btn-sm" type="submit" id="save-order" hidden>
                    <?= icon('check', 15) ?> Save new order
                </button>
            </div>
        </form>

        <?php foreach ($rows as $row): ?>
            <form method="post" action="<?= e($self) ?>" id="delete-<?= (int) $row['id'] ?>" class="visually-hidden">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
            </form>
        <?php endforeach; ?>
    <?php endif; ?>

<?php endif; ?>

<?php admin_foot(); ?>
