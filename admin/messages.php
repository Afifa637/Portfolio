<?php

/**
 * Contact inbox.
 *
 * Messages arrive here and by email. The database copy is the durable record —
 * if SMTP is misconfigured or the mail bounces, nothing is lost.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_admin();
require_once __DIR__ . '/_helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_require_post_csrf();

    $action = (string) ($_POST['action'] ?? '');
    $id     = (int) ($_POST['id'] ?? 0);

    if ($action === 'delete') {
        $ok = Database::execute('DELETE FROM contact_messages WHERE id = ?', [$id]);
        admin_redirect('messages.php', $ok ? 'Message deleted.' : 'Could not delete that message.', $ok);
    }

    if ($action === 'toggle_read') {
        Database::execute('UPDATE contact_messages SET is_read = 1 - is_read WHERE id = ?', [$id]);
        admin_redirect('messages.php', 'Updated.');
    }

    if ($action === 'mark_all') {
        Database::execute('UPDATE contact_messages SET is_read = 1 WHERE is_read = 0');
        admin_redirect('messages.php', 'All messages marked as read.');
    }
}

$filter = ($_GET['show'] ?? '') === 'unread' ? 'unread' : 'all';

$messages = Database::all(
    'SELECT * FROM contact_messages'
    . ($filter === 'unread' ? ' WHERE is_read = 0' : '')
    . ' ORDER BY created_at DESC, id DESC'
);

$unread = (int) (Database::first('SELECT COUNT(*) AS n FROM contact_messages WHERE is_read = 0')['n'] ?? 0);

admin_head('Messages');

?>
<div class="admin-head">
    <p class="admin-blurb">
        <?= $unread > 0
            ? '<strong>' . $unread . '</strong> unread.'
            : 'Everything read.' ?>
        Messages are stored here and emailed to you, so neither can lose one.
    </p>

    <div style="display:flex;gap:var(--sp-2);flex-wrap:wrap">
        <a class="btn btn-sm <?= $filter === 'all' ? 'btn-primary' : 'btn-ghost' ?>" href="messages.php">All</a>
        <a class="btn btn-sm <?= $filter === 'unread' ? 'btn-primary' : 'btn-ghost' ?>" href="messages.php?show=unread">Unread</a>
        <?php if ($unread > 0): ?>
            <button class="btn btn-sm btn-ghost" type="submit" form="mark-all">Mark all read</button>
        <?php endif; ?>
    </div>
</div>

<form method="post" action="messages.php" id="mark-all" class="visually-hidden">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="mark_all">
</form>

<?php if ($messages === []): ?>
    <p class="admin-empty">
        <?= $filter === 'unread' ? 'No unread messages.' : 'No messages yet.' ?>
    </p>
<?php else: ?>
    <div class="msg-list">
        <?php foreach ($messages as $message): ?>
            <?php $id = (int) $message['id']; ?>
            <article class="msg<?= empty($message['is_read']) ? ' is-unread' : '' ?>">
                <div class="msg-top">
                    <strong><?= e((string) $message['name']) ?></strong>
                    <a class="row-muted" href="mailto:<?= e((string) $message['email']) ?>"><?= e((string) $message['email']) ?></a>
                    <?php if (!empty($message['purpose'])): ?>
                        <span class="row-flag"><?= e((string) $message['purpose']) ?></span>
                    <?php endif; ?>
                    <?php if (empty($message['is_read'])): ?>
                        <span class="row-flag">New</span>
                    <?php endif; ?>
                    <time class="when" datetime="<?= e((string) $message['created_at']) ?>">
                        <?= e(time_ago((string) $message['created_at'])) ?>
                    </time>
                </div>

                <h3 style="font-size:var(--fs-md)"><?= e((string) $message['subject']) ?></h3>
                <p class="msg-body"><?= e((string) $message['message']) ?></p>

                <div class="msg-actions">
                    <a class="btn btn-sm btn-primary"
                       href="mailto:<?= e((string) $message['email']) ?>?subject=<?= rawurlencode('Re: ' . $message['subject']) ?>">
                        Reply
                    </a>
                    <button class="btn btn-sm btn-ghost" type="submit" form="read-<?= $id ?>">
                        Mark as <?= empty($message['is_read']) ? 'read' : 'unread' ?>
                    </button>
                    <button class="btn btn-sm btn-danger" type="submit" form="del-<?= $id ?>"
                            data-confirm="Delete this message permanently?">Delete</button>
                </div>
            </article>

            <form method="post" action="messages.php" id="read-<?= $id ?>" class="visually-hidden">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="toggle_read">
                <input type="hidden" name="id" value="<?= $id ?>">
            </form>
            <form method="post" action="messages.php" id="del-<?= $id ?>" class="visually-hidden">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $id ?>">
            </form>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php admin_foot(); ?>
