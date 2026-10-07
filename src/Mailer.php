<?php

/**
 * Outbound mail.
 *
 * Uses PHPMailer over SMTP when MAIL_HOST is configured, and falls back to the
 * native mail() function otherwise. Both paths are optional: a contact message
 * is persisted to the database first, so a mail failure never loses the message.
 */

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as MailException;

final class Mailer
{
    public static function configured(): bool
    {
        return (string) env('MAIL_HOST', '') !== '';
    }

    /**
     * Send the notification for a submitted contact message.
     *
     * @param array{name:string,email:string,subject:string,message:string,purpose:string} $data
     */
    public static function sendContactNotification(array $data): bool
    {
        $to = (string) env('MAIL_TO', (string) Content::get('identity.email', ''));

        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $subject = 'Portfolio enquiry: ' . $data['subject'];
        $html    = self::body($data);
        $text    = strip_tags(str_replace(['<br>', '</p>'], "\n", $html));

        return self::configured()
            ? self::viaSmtp($to, $subject, $html, $text, $data['email'], $data['name'])
            : self::viaMailFunction($to, $subject, $text, $data['email']);
    }

    private static function viaSmtp(
        string $to,
        string $subject,
        string $html,
        string $text,
        string $replyTo,
        string $replyName
    ): bool {
        if (!class_exists(PHPMailer::class)) {
            return false;
        }

        try {
            $mail = new PHPMailer(true);

            $mail->isSMTP();
            $mail->Host       = (string) env('MAIL_HOST', '');
            $mail->Port       = (int) env('MAIL_PORT', 587);
            $mail->SMTPAuth   = true;
            $mail->Username   = (string) env('MAIL_USERNAME', '');
            $mail->Password   = (string) env('MAIL_PASSWORD', '');
            $mail->CharSet    = 'UTF-8';
            $mail->Timeout    = 10;
            $mail->SMTPDebug  = SMTP::DEBUG_OFF;

            $encryption = strtolower((string) env('MAIL_ENCRYPTION', 'tls'));

            if ($encryption === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($encryption !== 'none') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            $mail->setFrom(
                (string) env('MAIL_FROM_ADDRESS', (string) env('MAIL_USERNAME', $to)),
                (string) env('MAIL_FROM_NAME', 'Portfolio Website')
            );
            $mail->addAddress($to);
            $mail->addReplyTo($replyTo, $replyName !== '' ? $replyName : $replyTo);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $html;
            $mail->AltBody = $text;

            return $mail->send();
        } catch (MailException | Throwable $e) {
            error_log('[portfolio] SMTP send failed: ' . $e->getMessage());
            return false;
        }
    }

    private static function viaMailFunction(string $to, string $subject, string $text, string $replyTo): bool
    {
        if (!function_exists('mail')) {
            return false;
        }

        $from = (string) env('MAIL_FROM_ADDRESS', 'no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));

        // Header values are newline-stripped to prevent header injection.
        $headers = implode("\r\n", [
            'From: ' . self::sanitiseHeader($from),
            'Reply-To: ' . self::sanitiseHeader($replyTo),
            'Content-Type: text/plain; charset=UTF-8',
            'MIME-Version: 1.0',
        ]);

        return @mail($to, self::sanitiseHeader($subject), $text, $headers);
    }

    private static function sanitiseHeader(string $value): string
    {
        return trim(str_replace(["\r", "\n", "%0a", "%0d"], '', $value));
    }

    /** @param array{name:string,email:string,subject:string,message:string,purpose:string} $d */
    private static function body(array $d): string
    {
        return '<div style="font-family:system-ui,sans-serif;line-height:1.6;color:#111">'
            . '<h2 style="margin:0 0 16px">New portfolio enquiry</h2>'
            . '<p><strong>Name:</strong> ' . e($d['name']) . '</p>'
            . '<p><strong>Email:</strong> ' . e($d['email']) . '</p>'
            . ($d['purpose'] !== '' ? '<p><strong>Purpose:</strong> ' . e($d['purpose']) . '</p>' : '')
            . '<p><strong>Subject:</strong> ' . e($d['subject']) . '</p>'
            . '<hr style="border:0;border-top:1px solid #ddd;margin:16px 0">'
            . '<p>' . nl2br(e($d['message'])) . '</p>'
            . '</div>';
    }
}
