<?php

/**
 * Contact: the conversion section.
 *
 * The form posts to the same URL. JavaScript intercepts and submits via fetch
 * for an inline response; without JavaScript the ordinary POST still works and
 * the result arrives as a flash message after redirect.
 */

declare(strict_types=1);

$contact  = Content::get('contact', []);
$identity = Content::get('identity', []);

$success = flash('contact_success');
$error   = flash('contact_error');
$errors  = $_SESSION['_flash']['contact_errors'] ?? [];
$old     = $_SESSION['_flash']['contact_old'] ?? [];

unset($_SESSION['_flash']['contact_errors'], $_SESSION['_flash']['contact_old']);

/** Render one field's stored value after a failed non-JS submission. */
$oldValue = static fn(string $key): string => e((string) ($old[$key] ?? ''));
$fieldErr = static fn(string $key): string => (string) ($errors[$key] ?? '');

?>
<section class="section" id="contact">
    <div class="container">
        <header class="section-head" data-reveal>
            <p class="eyebrow"><?= icon('send', 13) ?> Contact</p>
            <h2 class="section-title"><?= e($contact['heading']) ?></h2>
            <p class="section-lead"><?= e($contact['lead']) ?></p>
        </header>

        <div class="contact-grid">
            <div data-reveal>
                <div class="channel-list">
                    <?php foreach ($contact['channels'] as $channel): ?>
                        <?php $tag = $channel['href'] !== '' ? 'a' : 'div'; ?>
                        <<?= $tag ?> class="channel"
                            <?php if ($channel['href'] !== ''): ?>
                                href="<?= e($channel['href']) ?>"
                                <?= str_starts_with($channel['href'], 'mailto:') ? '' : 'target="_blank" rel="noopener noreferrer"' ?>
                            <?php endif; ?>>
                            <span class="ico"><?= icon($channel['icon'], 18) ?></span>
                            <span>
                                <span class="label"><?= e($channel['label']) ?></span>
                                <span class="value"><?= e($channel['value']) ?></span>
                            </span>
                        </<?= $tag ?>>
                    <?php endforeach; ?>
                </div>

                <div class="hero-actions" style="margin-top:var(--sp-4)">
                    <button class="btn btn-sm btn-ghost" type="button"
                            data-copy="<?= e($identity['email']) ?>">
                        <?= icon('copy', 15) ?> <span data-copy-label>Copy email</span>
                    </button>
                    <a class="btn btn-sm btn-ghost" href="<?= e($identity['resume']) ?>">
                        <?= icon('download', 15) ?> CV
                    </a>
                </div>

                <p class="form-note" style="margin-top:var(--sp-5)">
                    <?= icon('check', 15) ?>
                    <span>Usually replies within a day or two. Based in <?= e($identity['location']) ?>, open to remote work.</span>
                </p>
            </div>

            <form class="form" id="contact-form" method="post" action="<?= e(url('#contact')) ?>" novalidate data-reveal>
                <?php if ($success !== null): ?>
                    <p class="alert alert-ok" role="status"><?= icon('check', 18) ?> <span><?= e($success) ?></span></p>
                <?php elseif ($error !== null): ?>
                    <p class="alert alert-err" role="alert"><?= icon('x', 18) ?> <span><?= e($error) ?></span></p>
                <?php endif; ?>

                <p class="alert" id="form-status" hidden></p>

                <?= csrf_field() ?>
                <input type="hidden" name="rendered_at" value="<?= e((string) time()) ?>">

                <div class="hp" aria-hidden="true">
                    <label for="website">Leave this field empty</label>
                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                </div>

                <div class="form-row">
                    <div class="field" data-invalid="<?= $fieldErr('name') !== '' ? 'true' : 'false' ?>">
                        <label for="name">Name <span class="req" aria-hidden="true">*</span></label>
                        <input type="text" id="name" name="name" required autocomplete="name"
                               maxlength="120" placeholder="Your name" value="<?= $oldValue('name') ?>">
                        <span class="field-error"><?= e($fieldErr('name')) ?></span>
                    </div>

                    <div class="field" data-invalid="<?= $fieldErr('email') !== '' ? 'true' : 'false' ?>">
                        <label for="email">Email <span class="req" aria-hidden="true">*</span></label>
                        <input type="email" id="email" name="email" required autocomplete="email"
                               maxlength="200" placeholder="you@company.com" value="<?= $oldValue('email') ?>">
                        <span class="field-error"><?= e($fieldErr('email')) ?></span>
                    </div>
                </div>

                <div class="field">
                    <label for="purpose">What is this about?</label>
                    <select id="purpose" name="purpose">
                        <option value="">Select one…</option>
                        <?php foreach ($contact['purposes'] as $purpose): ?>
                            <option value="<?= e($purpose) ?>" <?= ($old['purpose'] ?? '') === $purpose ? 'selected' : '' ?>>
                                <?= e($purpose) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="field-error"></span>
                </div>

                <div class="field" data-invalid="<?= $fieldErr('subject') !== '' ? 'true' : 'false' ?>">
                    <label for="subject">Subject <span class="req" aria-hidden="true">*</span></label>
                    <input type="text" id="subject" name="subject" required maxlength="200"
                           placeholder="Backend internship — Acme Ltd" value="<?= $oldValue('subject') ?>">
                    <span class="field-error"><?= e($fieldErr('subject')) ?></span>
                </div>

                <div class="field" data-invalid="<?= $fieldErr('message') !== '' ? 'true' : 'false' ?>">
                    <label for="message">Message <span class="req" aria-hidden="true">*</span></label>
                    <textarea id="message" name="message" required maxlength="5000"
                              placeholder="Tell me about the role or project…"><?= $oldValue('message') ?></textarea>
                    <span class="field-error"><?= e($fieldErr('message')) ?></span>
                </div>

                <div class="hero-actions" style="margin:0">
                    <button class="btn btn-primary" type="submit">
                        <?= icon('send', 17) ?> Send message
                    </button>
                </div>

                <p class="form-note">
                    <?= icon('check', 15) ?>
                    <span>Your details are used only to reply. No newsletter, no third parties.</span>
                </p>
            </form>
        </div>
    </div>
</section>
