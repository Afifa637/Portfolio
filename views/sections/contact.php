<?php

/**
 * 10 — Contact.
 *
 * The form posts to the same URL. JavaScript submits it with fetch for an
 * inline success state; without JavaScript the ordinary POST works and the
 * result arrives as a flash message. Messages are stored and emailed — see
 * ContactHandler — so a mail outage cannot lose one.
 */

declare(strict_types=1);

$contact  = Content::get('contact', []);
$identity = Content::get('identity', []);
$services = Content::get('services', []);
$socials  = Content::get('socials', []);
$status   = Content::get('status', []);
$metrics  = Knowledge::metrics();

$success = flash('contact_success');
$error   = flash('contact_error');
$errors  = $_SESSION['_flash']['contact_errors'] ?? [];
$old     = $_SESSION['_flash']['contact_old'] ?? [];

unset($_SESSION['_flash']['contact_errors'], $_SESSION['_flash']['contact_old']);

$value = static fn(string $k): string => e((string) ($old[$k] ?? ''));
$err   = static fn(string $k): string => (string) ($errors[$k] ?? '');

$byIcon = [];
foreach ($socials as $s) {
    $byIcon[$s['icon']] = $s;
}

?>
<section class="section contact" id="contact" data-section="contact" data-label="10 / Contact">
    <div class="container">
        <p class="label" style="margin-bottom:1.5rem"><b style="color:var(--amber);font-weight:500">10</b> / Contact</p>

        <h2 class="contact-title" data-reveal="lines">
            <span class="ln" style="--i:0"><span>Have something</span></span>
            <span class="ln" style="--i:1"><span><span class="serif">worth building?</span></span></span>
        </h2>

        <div class="contact-grid">
            <div data-reveal>
                <div class="panel status-card">
                    <div class="panel-head">
                        <strong>AFIFA.DEV</strong>
                        <span class="label"><span class="live"></span> live</span>
                    </div>
                    <dl class="kv">
                        <div><dt>Status</dt><dd><?= !empty($identity['available']) ? 'Available' : 'Busy' ?></dd></div>
                        <div><dt>Focus</dt><dd><?= e((string) ($status['focus'] ?? '')) ?></dd></div>
                        <div><dt>Mode</dt><dd><?= e((string) ($status['mode'] ?? '')) ?></dd></div>
                        <div><dt>Local time</dt>
                            <dd><time data-clock data-tz="<?= e((string) ($status['timezone'] ?? 'Asia/Dhaka')) ?>" data-seconds>—</time>
                                <span class="t-3"><?= e((string) ($status['timezone'] ?? '')) ?></span></dd></div>
                        <?php if ($metrics['repos'] !== null): ?>
                            <div><dt>Repos</dt><dd><?= (int) $metrics['repos'] ?> public</dd></div>
                        <?php endif; ?>
                        <div><dt>Theme</dt><dd data-theme-label>dark</dd></div>
                    </dl>
                </div>

                <div class="channels">
                    <div class="channel">
                        <span class="k">Email</span>
                        <a class="v ulink" href="mailto:<?= e($identity['email']) ?>"><?= e($identity['email']) ?></a>
                        <button class="copy-btn" type="button" data-copy="<?= e($identity['email']) ?>" aria-label="Copy email address">
                            <span class="ic-copy"><?= icon('copy', 15) ?></span>
                            <span class="ic-check"><?= icon('check', 15) ?></span>
                        </button>
                    </div>
                    <?php foreach (['github' => 'GitHub', 'linkedin' => 'LinkedIn'] as $iconKey => $label): ?>
                        <?php if (isset($byIcon[$iconKey])): ?>
                            <div class="channel">
                                <span class="k"><?= e($label) ?></span>
                                <a class="v ulink" href="<?= e($byIcon[$iconKey]['url']) ?>" target="_blank" rel="noopener noreferrer"
                                   data-cursor="external" <?= $iconKey === 'github' ? 'data-track="github"' : '' ?>>
                                    <?= e(preg_replace('#^https?://(www\.)?#', '', rtrim($byIcon[$iconKey]['url'], '/'))) ?>
                                </a>
                                <?= icon('arrow-up-right', 15, 't-3') ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <div class="channel">
                        <span class="k">Résumé</span>
                        <a class="v ulink" href="<?= e(url(ltrim((string) $identity['resume'], '/'))) ?>" data-track="resume">Download PDF</a>
                        <?= icon('download', 15, 't-3') ?>
                    </div>
                </div>

                <?php if ($services !== []): ?>
                    <p class="label" style="margin-top:1.8rem">What I can help with</p>
                    <ul class="services-mini" role="list">
                        <?php foreach ($services as $service): ?>
                            <li><?= e($service['title']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <div class="card contact-form" data-reveal>
                <form class="form" id="contact-form" method="post" action="<?= e(url()) ?>#contact" novalidate>
                    <?php if ($success !== null): ?>
                        <p class="alert alert-ok" role="status"><?= icon('check', 18) ?><span><?= e($success) ?></span></p>
                    <?php elseif ($error !== null): ?>
                        <p class="alert alert-err" role="alert"><?= icon('x', 18) ?><span><?= e($error) ?></span></p>
                    <?php endif; ?>

                    <p class="alert" id="form-status" hidden></p>

                    <?= csrf_field() ?>
                    <input type="hidden" name="rendered_at" value="<?= time() ?>">
                    <div class="hp" aria-hidden="true">
                        <label for="website">Leave this field empty</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <fieldset class="choices field" data-invalid="<?= $err('purpose') !== '' ? 'true' : 'false' ?>">
                        <legend class="field-label">Reason for contact</legend>
                        <?php foreach ($contact['purposes'] as $i => $purpose): ?>
                            <label class="choice">
                                <input type="radio" name="purpose" value="<?= e($purpose) ?>"
                                    <?= (($old['purpose'] ?? '') === $purpose || (($old['purpose'] ?? '') === '' && $i === 0)) ? 'checked' : '' ?>>
                                <span><?= e($purpose) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </fieldset>

                    <div class="form-row">
                        <div class="field" data-invalid="<?= $err('name') !== '' ? 'true' : 'false' ?>">
                            <label for="name">Name <span class="req" aria-hidden="true">*</span></label>
                            <input type="text" id="name" name="name" required maxlength="120" autocomplete="name"
                                   placeholder="Your name" value="<?= $value('name') ?>" aria-describedby="name-err">
                            <span class="field-error" id="name-err"><?= e($err('name')) ?></span>
                        </div>
                        <div class="field" data-invalid="<?= $err('email') !== '' ? 'true' : 'false' ?>">
                            <label for="email">Email <span class="req" aria-hidden="true">*</span></label>
                            <input type="email" id="email" name="email" required maxlength="190" autocomplete="email"
                                   placeholder="you@company.com" value="<?= $value('email') ?>" aria-describedby="email-err">
                            <span class="field-error" id="email-err"><?= e($err('email')) ?></span>
                        </div>
                    </div>

                    <div class="field" data-invalid="<?= $err('message') !== '' ? 'true' : 'false' ?>">
                        <label for="message">Message <span class="req" aria-hidden="true">*</span></label>
                        <textarea id="message" name="message" required maxlength="5000" aria-describedby="message-err"
                                  placeholder="Tell me about the role, the team, or the thing you want built."><?= $value('message') ?></textarea>
                        <span class="field-error" id="message-err"><?= e($err('message')) ?></span>
                    </div>

                    <div class="hero-actions" style="align-items:center">
                        <button class="btn btn-primary" type="submit" data-magnetic>
                            <?= icon('send', 16) ?> Send message
                        </button>
                        <span class="field-hint">Saved and delivered to my inbox. I reply within a day or two.</span>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
