<?php

/**
 * Project manager.
 *
 * Bespoke rather than generated, because a project is more than a flat row:
 * it carries a repeatable list of feature bullets, an uploaded screenshot, a
 * publish toggle, and a link to live GitHub statistics.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_admin();
require_once __DIR__ . '/_helpers.php';
require_once __DIR__ . '/_resources.php';

$categories = [];

foreach (Database::all('SELECT slug, label FROM project_categories ORDER BY order_no, id') as $row) {
    $categories[(string) $row['slug']] = (string) $row['label'];
}

/**
 * Architecture layers arrive as three parallel arrays (layer, tech, role).
 * Rows without a layer name are dropped; the result is stored as JSON.
 */
function admin_architecture_from_post(): string
{
    $names = (array) ($_POST['arch_layer'] ?? []);
    $techs = (array) ($_POST['arch_tech'] ?? []);
    $roles = (array) ($_POST['arch_role'] ?? []);
    $rows  = [];

    foreach ($names as $i => $name) {
        $name = trim((string) $name);

        if ($name === '') {
            continue;
        }

        $rows[] = [
            'layer' => $name,
            'tech'  => trim((string) ($techs[$i] ?? '')),
            'role'  => trim((string) ($roles[$i] ?? '')),
        ];
    }

    return $rows === [] ? '' : (string) json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

/* ------------------------------------------------------------- actions ---- */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_require_post_csrf();

    $action = (string) ($_POST['action'] ?? 'save');

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        // project_features cascades on delete via its foreign key.
        $ok = Database::execute('DELETE FROM projects WHERE id = ?', [$id]);

        admin_redirect('projects.php', $ok ? 'Project deleted.' : 'Could not delete that project.', $ok);
    }

    if ($action === 'reorder') {
        foreach (array_map('intval', (array) ($_POST['order'] ?? [])) as $position => $id) {
            Database::execute('UPDATE projects SET order_no = ? WHERE id = ?', [$position, $id]);
        }

        admin_redirect('projects.php', 'Order saved.');
    }

    if ($action === 'toggle') {
        $id = (int) ($_POST['id'] ?? 0);
        $field = ($_POST['field'] ?? '') === 'featured' ? 'featured' : 'is_published';

        Database::execute("UPDATE projects SET `{$field}` = 1 - `{$field}` WHERE id = ?", [$id]);

        admin_redirect('projects.php', 'Updated.');
    }

    /* --- create or update --------------------------------------------- */

    $id    = (int) ($_POST['id'] ?? 0);
    $title = trim((string) ($_POST['title'] ?? ''));

    if ($title === '') {
        admin_redirect('projects.php' . ($id ? '?edit=' . $id : '?new=1'), 'A project needs a title.', false);
    }

    $image = trim((string) ($_POST['image'] ?? ''));

    try {
        $uploaded = admin_store_upload('image_upload');

        if ($uploaded !== null) {
            $image = $uploaded;
        }
    } catch (RuntimeException $e) {
        admin_redirect('projects.php' . ($id ? '?edit=' . $id : '?new=1'), $e->getMessage(), false);
    }

    $stack = implode(', ', array_filter(array_map(
        'trim',
        preg_split('/\r?\n/', (string) ($_POST['skills_used'] ?? '')) ?: []
    )));

    $data = [
        'title'                => $title,
        'slug'                 => Content::slugify((string) ($_POST['slug'] ?? '') ?: $title),
        'subtitle'             => trim((string) ($_POST['subtitle'] ?? '')),
        'category'             => trim((string) ($_POST['category'] ?? 'fullstack')),
        'image'                => $image,
        'summary'              => trim((string) ($_POST['summary'] ?? '')),
        'problem'              => trim((string) ($_POST['problem'] ?? '')),
        'challenges'           => trim((string) ($_POST['challenges'] ?? '')),
        'outcome'              => trim((string) ($_POST['outcome'] ?? '')),
        'learned'              => trim((string) ($_POST['learned'] ?? '')),
        'goal'                 => trim((string) ($_POST['goal'] ?? '')),
        'decisions'            => trim((string) ($_POST['decisions'] ?? '')),
        'security_notes'       => trim((string) ($_POST['security_notes'] ?? '')),
        'future_improvements'  => trim((string) ($_POST['future_improvements'] ?? '')),
        'demo_request'         => trim((string) ($_POST['demo_request'] ?? '')),
        'architecture'         => admin_architecture_from_post(),
        'skills_used'          => $stack,
        'role'                 => trim((string) ($_POST['role'] ?? '')),
        'year'                 => trim((string) ($_POST['year'] ?? '')),
        'view_link'            => trim((string) ($_POST['view_link'] ?? '')),
        'repo_name'            => trim((string) ($_POST['repo_name'] ?? '')),
        'featured'             => isset($_POST['featured']) ? 1 : 0,
        'is_published'         => isset($_POST['is_published']) ? 1 : 0,
    ];

    if ($id > 0) {
        $assignments = implode(', ', array_map(static fn($c) => "`{$c}` = ?", array_keys($data)));
        $ok = Database::execute(
            "UPDATE projects SET {$assignments} WHERE id = ?",
            [...array_values($data), $id]
        );
    } else {
        $data['order_no'] = (int) (Database::first('SELECT COALESCE(MAX(order_no), -1) + 1 AS n FROM projects')['n'] ?? 0);
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $columnList   = '`' . implode('`, `', array_keys($data)) . '`';

        $ok = Database::execute("INSERT INTO projects ({$columnList}) VALUES ({$placeholders})", array_values($data));
        $id = $ok ? (int) (Database::connection()?->insert_id ?? 0) : 0;
    }

    // Features are replaced wholesale — simpler and safer than diffing, and the
    // list is short enough that the cost is irrelevant.
    if ($ok && $id > 0) {
        Database::execute('DELETE FROM project_features WHERE project_id = ?', [$id]);

        $features = array_filter(array_map('trim', (array) ($_POST['features'] ?? [])));
        $position = 0;

        foreach ($features as $feature) {
            Database::execute(
                'INSERT INTO project_features (project_id, feature, order_no) VALUES (?, ?, ?)',
                [$id, $feature, $position++]
            );
        }
    }

    admin_redirect('projects.php', $ok ? 'Project saved.' : 'Could not save that project.', (bool) $ok);
}

/* --------------------------------------------------------------- render ---- */

$editId   = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$creating = isset($_GET['new']);
$project  = $editId > 0 ? Database::first('SELECT * FROM projects WHERE id = ?', [$editId]) : null;

$features = $project
    ? array_column(
        Database::all('SELECT feature FROM project_features WHERE project_id = ? ORDER BY order_no, id', [$editId]),
        'feature'
    )
    : [];

$rows = Database::all('SELECT * FROM projects ORDER BY order_no, id');

admin_head($project ? 'Edit project' : ($creating ? 'New project' : 'Projects'));

?>
<div class="admin-head">
    <p class="admin-blurb">
        Each project becomes a card and a full case-study panel. The more of the story you fill in,
        the more convincing it reads — reviewers care most about the problem and what you learned.
    </p>
    <?php if (!$creating && !$project): ?>
        <a class="btn btn-primary" href="projects.php?new=1"><?= icon('check', 16) ?> Add project</a>
    <?php endif; ?>
</div>

<?php if ($creating || $project): ?>

    <form class="admin-form" method="post" action="projects.php" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <?php if ($project): ?>
            <input type="hidden" name="id" value="<?= (int) $project['id'] ?>">
        <?php endif; ?>

        <section class="card" style="display:grid;gap:var(--sp-4)">
            <h2 style="font-size:var(--fs-lg);border:0;padding:0">The basics</h2>

            <div class="admin-form-grid">
                <?php
                admin_field(['name' => 'title', 'label' => 'Title', 'required' => true, 'maxlength' => 190], $project['title'] ?? '');
                admin_field(['name' => 'subtitle', 'label' => 'One-line descriptor', 'maxlength' => 255,
                             'placeholder' => 'Luxury watch resale marketplace'], $project['subtitle'] ?? '');
                admin_field(['name' => 'category', 'label' => 'Category', 'type' => 'select',
                             'options' => $categories, 'required' => true], $project['category'] ?? 'fullstack');
                admin_field(['name' => 'year', 'label' => 'Year', 'maxlength' => 16, 'placeholder' => '2026'], $project['year'] ?? '');
                admin_field(['name' => 'role', 'label' => 'Your role', 'maxlength' => 190,
                             'placeholder' => 'Sole developer'], $project['role'] ?? '');
                admin_field(['name' => 'repo_name', 'label' => 'GitHub repository', 'maxlength' => 190,
                             'hint' => 'Repository name only. Pulls in live language, stars and last-commit date.',
                             'placeholder' => 'Amar-Ration'], $project['repo_name'] ?? '');
                admin_field(['name' => 'view_link', 'label' => 'Live demo URL', 'type' => 'url', 'maxlength' => 255,
                             'hint' => 'Leave blank if there is nothing deployed'], $project['view_link'] ?? '');
                admin_field(['name' => 'slug', 'label' => 'URL slug', 'maxlength' => 190,
                             'hint' => 'Used for the #project-… deep link. Generated from the title if blank.'], $project['slug'] ?? '');
                ?>
            </div>

            <div class="admin-form-grid">
                <?php
                admin_field(['name' => 'featured', 'label' => 'Featured', 'type' => 'bool',
                             'on_label' => 'Highlight this project'], $project['featured'] ?? 0);
                admin_field(['name' => 'is_published', 'label' => 'Visible', 'type' => 'bool',
                             'on_label' => 'Show on the site'], $project['is_published'] ?? 1);
                ?>
            </div>

            <?php admin_field(['name' => 'image', 'label' => 'Screenshot', 'type' => 'image'], $project['image'] ?? ''); ?>
        </section>

        <section class="card" style="display:grid;gap:var(--sp-4)">
            <div>
                <h2 style="font-size:var(--fs-lg);border:0;padding:0">The case study</h2>
                <p class="field-hint" style="margin-top:4px">
                    This is what separates a portfolio from a list of links. Write plainly — what was hard,
                    and what you would do differently.
                </p>
            </div>

            <div class="admin-form-grid">
                <?php
                admin_field(['name' => 'summary', 'label' => 'Summary', 'type' => 'textarea', 'rows' => 3,
                             'hint' => 'Shown on the card and at the top of the case study'], $project['summary'] ?? '');
                admin_field(['name' => 'problem', 'label' => 'The problem', 'type' => 'textarea', 'rows' => 4,
                             'hint' => 'What needed solving, and why it was not trivial'], $project['problem'] ?? '');
                admin_field(['name' => 'challenges', 'label' => 'Hardest part', 'type' => 'textarea', 'rows' => 4,
                             'hint' => 'The specific thing that took longest to get right'], $project['challenges'] ?? '');
                admin_field(['name' => 'outcome', 'label' => 'Outcome', 'type' => 'textarea', 'rows' => 3], $project['outcome'] ?? '');
                admin_field(['name' => 'learned', 'label' => 'What you took from it', 'type' => 'textarea', 'rows' => 3], $project['learned'] ?? '');
                admin_field(['name' => 'skills_used', 'label' => 'Technologies', 'type' => 'list', 'rows' => 6,
                             'hint' => 'One per line'], $project['skills_used'] ?? '');
                ?>
            </div>

            <div class="admin-form-grid">
                <?php
                admin_field(['name' => 'goal', 'label' => 'Goal', 'type' => 'textarea', 'rows' => 3,
                             'hint' => 'What it set out to achieve. Left blank, the section is hidden.'], $project['goal'] ?? '');
                admin_field(['name' => 'decisions', 'label' => 'Engineering decisions', 'type' => 'textarea', 'rows' => 5,
                             'hint' => 'One decision per paragraph — a blank line between each.'], $project['decisions'] ?? '');
                admin_field(['name' => 'security_notes', 'label' => 'Security & validation', 'type' => 'textarea', 'rows' => 3],
                            $project['security_notes'] ?? '');
                admin_field(['name' => 'future_improvements', 'label' => 'What you would improve', 'type' => 'textarea', 'rows' => 3],
                            $project['future_improvements'] ?? '');
                ?>
            </div>
        </section>

        <section class="card" style="display:grid;gap:var(--sp-4)">
            <div>
                <h2 style="font-size:var(--fs-lg);border:0;padding:0">Architecture</h2>
                <p class="field-hint" style="margin-top:4px">
                    Layers from client to database. They drive the diagram on the case study, the visual on the
                    featured card, and — with an example request — the "Under the hood" simulator.
                </p>
            </div>

            <?php
            admin_field(['name' => 'demo_request', 'label' => 'Example request', 'maxlength' => 190,
                         'placeholder' => 'POST /api/auth/login',
                         'hint' => 'Method and path. Projects with one appear in "Under the hood".'], $project['demo_request'] ?? '');

            $layers = json_decode((string) ($project['architecture'] ?? ''), true);
            $layers = is_array($layers) && $layers !== [] ? $layers : [['layer' => '', 'tech' => '', 'role' => '']];
            ?>

            <div class="field">
                <label>Layers <span class="field-hint" style="text-transform:none;letter-spacing:0">— layer · technology · responsibility</span></label>
                <div class="repeater-list" id="arch-rows">
                    <?php foreach ($layers as $layer): ?>
                        <div class="repeater-row arch-row">
                            <input type="text" name="arch_layer[]" value="<?= e((string) ($layer['layer'] ?? '')) ?>" placeholder="Security filter chain">
                            <input type="text" name="arch_tech[]" value="<?= e((string) ($layer['tech'] ?? '')) ?>" placeholder="Spring Security, JWT">
                            <input type="text" name="arch_role[]" value="<?= e((string) ($layer['role'] ?? '')) ?>" placeholder="What this layer is responsible for">
                            <button type="button" class="btn btn-sm btn-danger" data-arch-remove aria-label="Remove layer">&times;</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="btn btn-sm btn-ghost" id="arch-add" style="justify-self:start">Add layer</button>
            </div>
        </section>

        <section class="card" style="display:grid;gap:var(--sp-4)">
            <div class="field" data-repeater="features">
                <label>What it does</label>
                <p class="field-hint">The bullet list inside the case study. One capability per row.</p>

                <div class="repeater-list" data-repeater-list>
                    <?php foreach ($features ?: [''] as $feature): ?>
                        <div class="repeater-row">
                            <input type="text" name="features[]" value="<?= e((string) $feature) ?>"
                                   placeholder="One feature per row">
                            <button type="button" class="btn btn-sm btn-danger" data-repeater-remove aria-label="Remove">&times;</button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button type="button" class="btn btn-sm btn-ghost" data-repeater-add style="justify-self:start">
                    <?= icon('check', 15) ?> Add row
                </button>
            </div>
        </section>

        <div class="admin-form-actions">
            <button class="btn btn-primary" type="submit"><?= icon('check', 16) ?> Save project</button>
            <a class="btn btn-ghost" href="projects.php">Cancel</a>
            <?php if ($project): ?>
                <a class="btn btn-ghost" href="<?= e(project_url((string) $project['slug'])) ?>"
                   target="_blank" rel="noopener"><?= icon('external', 16) ?> Preview</a>
            <?php endif; ?>
        </div>
    </form>

<?php else: ?>

    <?php if ($rows === []): ?>
        <p class="admin-empty">No projects yet. <a href="projects.php?new=1">Add the first one</a>.</p>
    <?php else: ?>
        <form method="post" action="projects.php" id="reorder-form">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="reorder">

            <div class="admin-table-wrap">
                <table class="admin-table" data-sortable>
                    <thead>
                        <tr>
                            <th class="col-handle"><span class="visually-hidden">Reorder</span></th>
                            <th>Project</th>
                            <th>Category</th>
                            <th>Status</th>
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
                                <td>
                                    <strong><?= e((string) $row['title']) ?></strong>
                                    <?php if (!empty($row['subtitle'])): ?>
                                        <br><span class="row-muted" style="font-size:var(--fs-sm)"><?= e((string) $row['subtitle']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= e($categories[$row['category']] ?? (string) $row['category']) ?></td>
                                <td>
                                    <div class="status-toggles">
                                        <button class="chip-toggle<?= !empty($row['featured']) ? ' is-on' : '' ?>"
                                                type="submit" form="feat-<?= (int) $row['id'] ?>"
                                                title="<?= !empty($row['featured']) ? 'Remove from featured' : 'Mark as featured' ?>">
                                            <?= icon('star', 12) ?> Featured
                                        </button>
                                        <button class="chip-toggle<?= !empty($row['is_published']) ? ' is-on' : '' ?>"
                                                type="submit" form="pub-<?= (int) $row['id'] ?>"
                                                title="<?= !empty($row['is_published']) ? 'Hide from the site' : 'Show on the site' ?>">
                                            <?= icon('check', 12) ?> <?= !empty($row['is_published']) ? 'Visible' : 'Hidden' ?>
                                        </button>
                                        <?php if (empty($row['problem'])): ?>
                                            <span class="row-muted" title="No case study written yet">Thin</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="col-actions">
                                    <a class="btn btn-sm btn-ghost" href="projects.php?edit=<?= (int) $row['id'] ?>">Edit</a>
                                    <button class="btn btn-sm btn-danger" type="submit" form="delete-<?= (int) $row['id'] ?>"
                                            data-confirm="Delete &quot;<?= e((string) $row['title']) ?>&quot;? This cannot be undone.">
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
            <form method="post" action="projects.php" id="delete-<?= (int) $row['id'] ?>" class="visually-hidden">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
            </form>
            <form method="post" action="projects.php" id="feat-<?= (int) $row['id'] ?>" class="visually-hidden">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="toggle">
                <input type="hidden" name="field" value="featured">
                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
            </form>
            <form method="post" action="projects.php" id="pub-<?= (int) $row['id'] ?>" class="visually-hidden">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="toggle">
                <input type="hidden" name="field" value="is_published">
                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
            </form>
        <?php endforeach; ?>
    <?php endif; ?>

<?php endif; ?>

<?php admin_foot(); ?>
