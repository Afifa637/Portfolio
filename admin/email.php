<?php

/**
 * Email delivery status and test.
 *
 * Contact messages are written to the database first and emailed second, so a
 * mail failure never loses an enquiry. This screen makes the mail half
 * verifiable: it reports exactly how delivery is configured and sends a real
 * test message, rather than leaving you to find out from a missed opportunity.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_admin();
require_once __DIR__ . '/_helpers.php';

$identity = Content::get('identity', []);
$to       = (string) env('MAIL_TO', (string) ($identity['email'] ?? ''));
$host     = (string) env('MAIL_HOST', '');
$username = (string) env('MAIL_USERNAME', '');
$from     = (string) env('MAIL_FROM_ADDRESS', $username);

$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_require_post_csrf();

    if (!rate_limit_ok('admin_mail_test', 5, 600)) {
        admin_redirect('email.php', 'Too many test emails. Wait a few minutes.', false);
    }

    $sent = Mailer::sendContactNotification([
        'name'    => 'Portfolio test',
        'email'   => $to !== '' ? $to : 'noreply@localhost',
        'subject' => 'Delivery test — ' . date('H:i, j M Y'),
        'message' => "This is a test message sent from your portfolio admin panel.\n\n"
            . "If it reached your inbox, the contact form will reach you too.\n\n"
            . 'Transport: ' . (Mailer::configured() ? 'SMTP via ' . $host : 'PHP mail()'),
        'purpose' => 'Delivery test',
    ]);

    admin_redirect(
        'email.php',
        $sent
            ? 'Test email sent to ' . $to . '. Check your inbox — and your spam folder if it is not there.'
            : 'Could not send. Check the settings below and your server error log for details.',
        $sent
    );
}

/* Configuration checks, worst problems first. */
$checks = [
    [
        'label' => 'Delivery address',
        'ok'    => $to !== '' && filter_var($to, FILTER_VALIDATE_EMAIL),
        'value' => $to !== '' ? $to : 'Not set',
        'fix'   => 'Set MAIL_TO in .env, or a public email under Site & SEO.',
    ],
    [
        'label' => 'Transport',
        'ok'    => Mailer::configured(),
        'value' => Mailer::configured() ? 'SMTP — ' . $host . ':' . (string) env('MAIL_PORT', 587) : 'PHP mail() fallback',
        'fix'   => 'SMTP is strongly recommended: mail() is often blocked, and when it does send, the message usually lands in spam.',
    ],
    [
        'label' => 'Authentication',
        'ok'    => $username !== '' && (string) env('MAIL_PASSWORD', '') !== '',
        'value' => $username !== '' ? $username : 'Not set',
        'fix'   => 'Set MAIL_USERNAME and MAIL_PASSWORD in .env.',
    ],
    [
        'label' => 'From address',
        'ok'    => $from !== '' && filter_var($from, FILTER_VALIDATE_EMAIL),
        'value' => $from !== '' ? $from : 'Not set',
        'fix'   => 'Set MAIL_FROM_ADDRESS. For Gmail it must match the authenticated account.',
    ],
    [
        'label' => 'Messages stored in the database',
        'ok'    => Database::hasTable('contact_messages'),
        'value' => Database::hasTable('contact_messages')
            ? (string) (Database::first('SELECT COUNT(*) AS n FROM contact_messages')['n'] ?? 0) . ' saved'
            : 'Table missing',
        'fix'   => 'Run database/schema.sql so enquiries survive a mail failure.',
    ],
];

$ready = Mailer::configured() && $to !== '';

admin_head('Email delivery');

?>
<div class="admin-head">
    <p class="admin-blurb">
        When someone fills in your contact form, the message is saved here <em>and</em> emailed to you.
        Both have to work for you to never miss an opportunity — this page proves the email half.
    </p>
</div>

<div class="admin-section">
    <h2>Status</h2>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr><th>Check</th><th>Current</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php foreach ($checks as $check): ?>
                    <tr>
                        <td><strong><?= e($check['label']) ?></strong></td>
                        <td class="row-muted"><?= e($check['value']) ?></td>
                        <td>
                            <?php if ($check['ok']): ?>
                                <span class="row-flag">OK</span>
                            <?php else: ?>
                                <span style="color:var(--danger)">Needs setup</span>
                                <br><span class="row-muted" style="font-size:var(--fs-sm)"><?= e($check['fix']) ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="admin-section">
    <h2>Send a test</h2>

    <form class="card admin-form" method="post" action="email.php">
        <?= csrf_field() ?>
        <p class="field-hint">
            Sends a real message to <strong><?= e($to !== '' ? $to : 'your address') ?></strong> using the
            same code path as the contact form.
        </p>
        <div class="admin-form-actions" style="border:0;padding:0">
            <button class="btn btn-primary" type="submit" <?= $to === '' ? 'disabled' : '' ?>>
                <?= icon('send', 16) ?> Send test email
            </button>
        </div>
    </form>
</div>

<?php if (!$ready): ?>
<div class="admin-section">
    <h2>Setting up Gmail</h2>

    <div class="card" style="display:grid;gap:var(--sp-4)">
        <p class="admin-blurb" style="margin:0">
            Gmail will not accept your normal password from an application. You need a 16-character
            <strong>App Password</strong>, which can be revoked without changing your account password.
        </p>

        <ol style="display:grid;gap:var(--sp-3);padding-left:1.2rem;color:var(--text-dim);line-height:1.7">
            <li>Turn on 2-Step Verification at
                <a href="https://myaccount.google.com/security" target="_blank" rel="noopener noreferrer">
                    myaccount.google.com/security</a>.</li>
            <li>Create an App Password at
                <a href="https://myaccount.google.com/apppasswords" target="_blank" rel="noopener noreferrer">
                    myaccount.google.com/apppasswords</a> — choose "Mail" as the app.</li>
            <li>Copy the 16 characters and put them in your <code>.env</code> file:</li>
        </ol>

<pre class="mono" style="padding:var(--sp-4);border:1px solid var(--border);border-radius:var(--r-md);background:var(--bg-elev);overflow-x:auto;font-size:var(--fs-sm);line-height:1.8"><code>MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=<?= e($identity['email'] ?? 'you@gmail.com') ?>

MAIL_PASSWORD=your-16-char-app-password
MAIL_FROM_ADDRESS=<?= e($identity['email'] ?? 'you@gmail.com') ?>

MAIL_FROM_NAME="Portfolio Website"
MAIL_TO=<?= e($identity['email'] ?? 'you@gmail.com') ?></code></pre>

        <p class="field-hint" style="margin:0">
            Then reload this page and send a test. The App Password is stored only in
            <code>.env</code>, which is excluded from Git and blocked by the web server.
        </p>
    </div>
</div>
<?php endif; ?>

<?php admin_foot(); ?>
