<?php

/**
 * Dashboard: what is on the site right now, and the quickest way to change it.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_admin();
require_once __DIR__ . '/_helpers.php';

/** Row count for a table, or null when the table does not exist. */
function admin_count(string $table): ?int
{
    if (!Database::hasTable($table)) {
        return null;
    }

    return (int) (Database::first("SELECT COUNT(*) AS n FROM `{$table}`")['n'] ?? 0);
}

$unread   = (int) (Database::first('SELECT COUNT(*) AS n FROM contact_messages WHERE is_read = 0')['n'] ?? 0);
$latest   = Database::all('SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 3');
$gh       = GitHub::data();
$identity = Content::get('identity', []);

$stats = [
    ['n' => admin_count('projects')   ?? 0, 'l' => 'Projects',   'href' => 'projects.php'],
    ['n' => admin_count('skills')     ?? 0, 'l' => 'Skills',     'href' => 'resource.php?r=skills'],
    ['n' => admin_count('education')  ?? 0, 'l' => 'Education',  'href' => 'resource.php?r=education'],
    ['n' => $unread,                        'l' => 'Unread messages', 'href' => 'messages.php'],
];

admin_head('Dashboard');

?>
<div class="admin-head">
    <p class="admin-blurb">
        Signed in as <strong><?= e((string) ($_SESSION['admin'] ?? '')) ?></strong>.
        Everything the public site shows is editable from here — changes are live immediately.
    </p>
</div>

<div class="admin-stats">
    <?php foreach ($stats as $stat): ?>
        <div class="admin-stat">
            <span class="n"><?= (int) $stat['n'] ?></span>
            <span class="l"><?= e($stat['l']) ?></span>
            <a href="<?= e($stat['href']) ?>">Manage →</a>
        </div>
    <?php endforeach; ?>
</div>

<div class="admin-section">
    <h2>Jump to</h2>
    <div class="admin-quick">
        <a href="settings.php">
            <?= icon('settings', 20) ?>
            <span><strong>Site &amp; SEO</strong><small>Name, pitch, metadata</small></span>
        </a>
        <a href="projects.php">
            <?= icon('layers', 20) ?>
            <span><strong>Projects</strong><small>Case studies and screenshots</small></span>
        </a>
        <a href="resource.php?r=skills">
            <?= icon('code', 20) ?>
            <span><strong>Skills</strong><small>Technologies by group</small></span>
        </a>
        <a href="media.php">
            <?= icon('copy', 20) ?>
            <span><strong>Media</strong><small>Upload and browse images</small></span>
        </a>
    </div>
</div>

<div class="admin-section">
    <h2>Recent messages</h2>

    <?php if ($latest === []): ?>
        <p class="admin-empty">No messages yet. The contact form delivers them here and by email.</p>
    <?php else: ?>
        <div class="msg-list">
            <?php foreach ($latest as $message): ?>
                <article class="msg<?= empty($message['is_read']) ? ' is-unread' : '' ?>">
                    <div class="msg-top">
                        <strong><?= e((string) $message['name']) ?></strong>
                        <span class="row-muted"><?= e((string) $message['email']) ?></span>
                        <?php if (empty($message['is_read'])): ?>
                            <span class="row-flag">New</span>
                        <?php endif; ?>
                        <span class="when"><?= e(time_ago((string) $message['created_at'])) ?></span>
                    </div>
                    <p class="row-muted"><?= e((string) $message['subject']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="admin-form-actions">
            <a class="btn btn-ghost btn-sm" href="messages.php">All messages <?= icon('arrow-right', 15) ?></a>
        </div>
    <?php endif; ?>
</div>

<div class="admin-section">
    <h2>System</h2>

    <div class="admin-quick">
        <div class="admin-stat">
            <span class="l">GitHub sync</span>
            <strong style="display:block;margin-top:4px">
                <?php if (!empty($gh['ok'])): ?>
                    <?= e((string) $gh['stats']['repos']) ?> repos ·
                    <?= $gh['stale'] ? 'cached' : 'synced ' . e(time_ago(date('c', (int) $gh['fetched_at']))) ?>
                <?php else: ?>
                    Unavailable — the site falls back to saved content
                <?php endif; ?>
            </strong>
        </div>

        <div class="admin-stat">
            <span class="l">Contact email</span>
            <strong style="display:block;margin-top:4px">
                <?php if (Mailer::configured()): ?>
                    SMTP configured
                <?php else: ?>
                    Using PHP mail() — set MAIL_HOST in .env for reliable delivery
                <?php endif; ?>
            </strong>
        </div>

        <div class="admin-stat">
            <span class="l">Delivering to</span>
            <strong style="display:block;margin-top:4px"><?= e((string) env('MAIL_TO', $identity['email'])) ?></strong>
        </div>
    </div>
</div>

<?php admin_foot(); ?>
