<?php

/**
 * Contact form processing.
 *
 * Runs the full submission pipeline — CSRF, rate limiting, honeypot, timing
 * check, validation — then persists the message and sends a notification.
 *
 * Responds with JSON when the request is an XHR (the front-end submits without
 * a reload) and with a POST/redirect/GET flash otherwise, so the form still
 * works with JavaScript disabled.
 */

declare(strict_types=1);

final class ContactHandler
{
    private const MAX_NAME    = 120;
    private const MAX_SUBJECT = 200;
    private const MAX_MESSAGE = 5000;

    /** Minimum seconds between form render and submit; below this it is a bot. */
    private const MIN_FILL_SECONDS = 3;

    public static function handle(): void
    {
        $isAjax = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';

        /* --------------------------------------------------------- csrf ---- */

        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            self::respond($isAjax, false, 'Your session expired. Please reload the page and try again.', 419);
        }

        /* --------------------------------------------------- rate limit ---- */

        if (!rate_limit_ok('contact_rate', 5, 600)) {
            self::respond($isAjax, false, 'Too many messages sent. Please try again in a few minutes.', 429);
        }

        /* ------------------------------------------------------ honeypot ---- */

        // Bots fill every field they find; a human never sees this one.
        if (trim((string) ($_POST['website'] ?? '')) !== '') {
            // Report success so the bot does not learn it was caught.
            self::respond($isAjax, true, 'Thank you — your message has been sent.');
        }

        // Instant submissions are automated.
        $renderedAt = (int) ($_POST['rendered_at'] ?? 0);

        if ($renderedAt > 0 && (time() - $renderedAt) < self::MIN_FILL_SECONDS) {
            self::respond($isAjax, true, 'Thank you — your message has been sent.');
        }

        /* ---------------------------------------------------- validation ---- */

        $name    = self::clean($_POST['name'] ?? '');
        $email   = self::clean($_POST['email'] ?? '');
        $subject = self::clean($_POST['subject'] ?? '');
        $message = trim((string) ($_POST['message'] ?? ''));
        $purpose = self::clean($_POST['purpose'] ?? '');

        $errors = [];

        if ($name === '') {
            $errors['name'] = 'Please enter your name.';
        } elseif (mb_strlen($name) > self::MAX_NAME) {
            $errors['name'] = 'That name is too long.';
        }

        if ($email === '') {
            $errors['email'] = 'Please enter your email address.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'That email address does not look valid.';
        }

        // Only accept a reason the form actually offered.
        $allowedPurposes = Content::get('contact.purposes', []);

        if ($purpose !== '' && $allowedPurposes !== [] && !in_array($purpose, $allowedPurposes, true)) {
            $purpose = '';
        }

        // The form asks for a reason rather than a free-text subject, so build a
        // readable one for the inbox and the email when none was sent.
        if ($subject === '') {
            $subject = ($purpose !== '' ? $purpose : 'Portfolio enquiry') . ' — ' . ($name !== '' ? $name : 'website visitor');
        }

        if (mb_strlen($subject) > self::MAX_SUBJECT) {
            $subject = mb_substr($subject, 0, self::MAX_SUBJECT);
        }

        if ($message === '') {
            $errors['message'] = 'Please write a message.';
        } elseif (mb_strlen($message) < 10) {
            $errors['message'] = 'Please write at least a sentence or two.';
        } elseif (mb_strlen($message) > self::MAX_MESSAGE) {
            $errors['message'] = 'That message is too long — please keep it under 5000 characters.';
        }

        if ($errors !== []) {
            self::respond($isAjax, false, 'Please check the highlighted fields.', 422, $errors);
        }

        /* ------------------------------------------------------- persist ---- */

        $stored = false;

        if (Database::hasTable('contact_messages')) {
            $body = $purpose !== ''
                ? $message . "\n\n— Purpose: " . $purpose
                : $message;

            $stored = Database::execute(
                'INSERT INTO contact_messages (name, email, subject, message, purpose, created_at)
                 VALUES (?, ?, ?, ?, ?, ?)',
                [$name, $email, $subject, $body, $purpose, date('Y-m-d H:i:s')]
            );
        }

        /* ---------------------------------------------------------- mail ---- */

        $mailed = Mailer::sendContactNotification(compact('name', 'email', 'subject', 'message', 'purpose'));

        // Only a total failure — neither saved nor sent — is reported as an error.
        if (!$stored && !$mailed) {
            error_log('[portfolio] Contact message could not be stored or emailed.');

            self::respond(
                $isAjax,
                false,
                'Something went wrong on our side. Please email ' . Content::get('identity.email') . ' directly.',
                500
            );
        }

        // Rotate the token so the same submission cannot be replayed. The new
        // one is handed back to the page so a second message can be sent
        // without a reload.
        unset($_SESSION['csrf_token']);

        self::respond($isAjax, true, 'Thank you — your message has been sent. I will reply soon.');
    }

    private static function clean(mixed $value): string
    {
        $value = is_string($value) ? $value : '';

        // Strip control characters, including the CR/LF used for header injection.
        return trim(preg_replace('/[\x00-\x1F\x7F]/u', '', $value) ?? '');
    }

    /** @param array<string, string> $errors */
    private static function respond(
        bool $isAjax,
        bool $ok,
        string $message,
        int $status = 200,
        array $errors = []
    ): never {
        if ($isAjax) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-store');

            echo json_encode([
                'ok'      => $ok,
                'message' => $message,
                'errors'  => $errors,
                // csrf_token() regenerates when the old one was cleared, so a
                // successful send hands the page a usable token for the next.
                'csrf'    => csrf_token(),
            ], JSON_UNESCAPED_SLASHES);

            exit;
        }

        flash($ok ? 'contact_success' : 'contact_error', $message);

        if ($errors !== []) {
            $_SESSION['_flash']['contact_errors'] = $errors;
            $_SESSION['_flash']['contact_old']    = [
                'name'    => (string) ($_POST['name'] ?? ''),
                'email'   => (string) ($_POST['email'] ?? ''),
                'subject' => (string) ($_POST['subject'] ?? ''),
                'message' => (string) ($_POST['message'] ?? ''),
                'purpose' => (string) ($_POST['purpose'] ?? ''),
            ];
        }

        header('Location: ' . url('#contact'), true, 303);
        exit;
    }
}
