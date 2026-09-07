<?php
/**
 * Mail helper — drop-in transactional email for Geprek Geh.
 *
 * Supports two transports via env vars:
 *   - GEPREK_MAIL_DRIVER=php  → native mail() (default; logs to mail.log in dev)
 *   - GEPREK_MAIL_DRIVER=smtp → SMTP via PHPMailer (if vendor available)
 *
 * Reads:
 *   GEPREK_MAIL_FROM        default "Geprek Geh <no-reply@geprekgeh.com>"
 *   GEPREK_MAIL_FROM_NAME   default "Geprek Geh"
 *   GEPREK_MAIL_LOG_PATH    default /var/www/html/geprek-geh/logs/mail.log
 *   (smtp)
 *   GEPREK_SMTP_HOST / PORT / USER / PASS / ENC (tls|ssl|none)
 */
class Mail {
    private static function from(): string {
        $env = getenv('GEPREK_MAIL_FROM');
        return $env ?: 'Geprek Geh <no-reply@geprekgeh.com>';
    }

    private static function logPath(): string {
        $env = getenv('GEPREK_MAIL_LOG_PATH');
        $path = $env ?: '/var/www/html/geprek-geh/logs/mail.log';
        if (!is_dir(dirname($path))) @mkdir(dirname($path), 0775, true);
        return $path;
    }

    /**
     * Send an email. Returns true on success, false on failure (caller can log/ignore).
     * $body may be HTML; a plain-text fallback is generated automatically.
     */
    public static function send(string $to, string $subject, string $htmlBody, ?string $plainBody = null): bool {
        $driver = strtolower(getenv('GEPREK_MAIL_DRIVER') ?: 'php');
        $plain = $plainBody ?? trim(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody)));

        if ($driver === 'smtp') {
            return self::sendSmtp($to, $subject, $htmlBody, $plain);
        }
        return self::sendPhp($to, $subject, $htmlBody, $plain);
    }

    /** Native mail() with proper headers; always logged for audit. */
    private static function sendPhp(string $to, string $subject, string $html, string $plain): bool {
        $boundary = 'gg-mix-' . bin2hex(random_bytes(8));
        $headers = [
            'From: ' . self::from(),
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
            'X-Mailer: GeprekGeh/1.0',
        ];
        $nl = "\r\n";
        $body = "";
        $body .= "--{$boundary}{$nl}Content-Type: text/plain; charset=utf-8{$nl}{$nl}{$plain}{$nl}{$nl}";
        $body .= "--{$boundary}{$nl}Content-Type: text/html; charset=utf-8{$nl}{$nl}{$html}{$nl}{$nl}";
        $body .= "--{$boundary}--{$nl}";

        $ok = @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, implode("\r\n", $headers), '-f' . self::parseEmail(self::from()));
        self::log($to, $subject, $html, $plain, $ok ? 'sent' : 'failed');
        return (bool) $ok;
    }

    /** SMTP via PHPMailer if vendor is available. */
    private static function sendSmtp(string $to, string $subject, string $html, string $plain): bool {
        $candidates = [
            __DIR__ . '/../vendor/PHPMailer/PHPMailer.php',
            __DIR__ . '/../vendor/phpmailer/PHPMailer.php',
        ];
        $loaded = false;
        foreach ($candidates as $f) {
            if (is_file($f)) { require_once $f; $loaded = true; break; }
        }
        if (!$loaded) {
            // Fallback to mail() if PHPMailer missing
            error_log('[Mail] SMTP driver requested but PHPMailer not found; falling back to mail()');
            return self::sendPhp($to, $subject, $html, $plain);
        }
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = getenv('GEPREK_SMTP_HOST') ?: 'localhost';
            $mail->Port       = (int) (getenv('GEPREK_SMTP_PORT') ?: 587);
            $mail->SMTPAuth   = (bool) getenv('GEPREK_SMTP_USER');
            if ($mail->SMTPAuth) {
                $mail->Username = getenv('GEPREK_SMTP_USER');
                $mail->Password = getenv('GEPREK_SMTP_PASS');
            }
            $enc = strtolower(getenv('GEPREK_SMTP_ENC') ?: 'tls');
            if ($enc === 'ssl') $mail->SMTPSecure = 'ssl';
            elseif ($enc === 'tls') $mail->SMTPSecure = 'tls';
            $fromAddr = self::parseEmail(self::from());
            $fromName = getenv('GEPREK_MAIL_FROM_NAME') ?: 'Geprek Geh';
            $mail->setFrom($fromAddr, $fromName);
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $html;
            $mail->AltBody = $plain;
            $ok = $mail->send();
            self::log($to, $subject, $html, $plain, $ok ? 'sent' : 'failed');
            return (bool) $ok;
        } catch (Exception $e) {
            error_log('[Mail] SMTP error: ' . $e->getMessage());
            self::log($to, $subject, $html, $plain, 'error: ' . $e->getMessage());
            return false;
        }
    }

    private static function parseEmail(string $from): string {
        if (preg_match('/<([^>]+)>/', $from, $m)) return trim($m[1]);
        return trim($from);
    }

    private static function log(string $to, string $subject, string $html, string $plain, string $status): void {
        $line = sprintf(
            "[%s] %s → %s | %s | subject=%s\n",
            date('c'),
            $status,
            $to,
            $_SERVER['REQUEST_URI'] ?? 'cli',
            $subject
        );
        @file_put_contents(self::logPath(), $line, FILE_APPEND);
    }

    /* ── Pre-built templates ───────────────────────────────────────── */

    public static function passwordReset(string $email, string $name, string $resetUrl, int $ttlMinutes = 60): bool {
        $subject = 'Reset password akun Geprek Geh';
        $html = self::wrap('Reset Password', "
            <p style=\"margin:0 0 18px 0;\">Halo <strong>" . htmlspecialchars($name) . "</strong>,</p>
            <p style=\"margin:0 0 18px 0;\">Kami menerima permintaan untuk reset password akunmu. Klik tombol di bawah untuk memilih password baru. Link berlaku selama {$ttlMinutes} menit.</p>
            <p style=\"margin:28px 0;text-align:center;\">
                <a href=\"" . htmlspecialchars($resetUrl) . "\" style=\"display:inline-block;padding:14px 28px;background:#D43E1B;color:#fff;text-decoration:none;border-radius:999px;font-weight:700;font-family:'Plus Jakarta Sans',sans-serif;\">Reset Password</a>
            </p>
            <p style=\"margin:18px 0;color:#8A7A65;font-size:13px;\">Atau salin link ini ke browser: <br><span style=\"color:#5A4D3D;word-break:break-all;\">" . htmlspecialchars($resetUrl) . "</span></p>
            <p style=\"margin:24px 0 0 0;color:#8A7A65;font-size:13px;\">Kalau kamu tidak meminta reset, abaikan email ini — passwordmu tetap aman.</p>
        ");
        return self::send($email, $subject, $html);
    }

    public static function orderCreated(string $email, string $name, array $order, array $items): bool {
        $subject = 'Pesanan #' . htmlspecialchars($order['invoice_no']) . ' diterima';
        $rows = '';
        foreach ($items as $it) {
            $rows .= '<tr><td style="padding:8px 12px;border-bottom:1px solid #EADFC8;">' . htmlspecialchars($it['name']) . '</td>'
                . '<td style="padding:8px 12px;border-bottom:1px solid #EADFC8;text-align:center;">' . (int) $it['quantity'] . '</td>'
                . '<td style="padding:8px 12px;border-bottom:1px solid #EADFC8;text-align:right;">Rp ' . number_format($it['price'] * $it['quantity'], 0, ',', '.') . '</td></tr>';
        }
        $html = self::wrap('Pesanan Diterima', "
            <p style=\"margin:0 0 18px 0;\">Halo <strong>" . htmlspecialchars($name) . "</strong>,</p>
            <p style=\"margin:0 0 18px 0;\">Pesanan kamu telah kami terima dan sedang disiapkan. Invoice: <strong>" . htmlspecialchars($order['invoice_no']) . "</strong></p>
            <table style=\"width:100%;border-collapse:collapse;margin:18px 0;\">{$rows}</table>
            <p style=\"margin:18px 0;text-align:right;font-size:18px;\"><strong>Total: Rp " . number_format($order['grand_total'], 0, ',', '.') . "</strong></p>
            <p style=\"margin:24px 0 0 0;color:#8A7A65;font-size:13px;\">Pantau status pesanan di halaman Pesanan Saya.</p>
        ");
        return self::send($email, $subject, $html);
    }

    public static function orderStatusChanged(string $email, string $name, string $invoice, string $statusLabel, ?string $note = null): bool {
        $subject = "Pesanan {$invoice}: {$statusLabel}";
        $extra = $note ? "<p style=\"margin:18px 0;color:#5A4D3D;\">" . htmlspecialchars($note) . "</p>" : '';
        $html = self::wrap("Status: {$statusLabel}", "
            <p style=\"margin:0 0 18px 0;\">Halo <strong>" . htmlspecialchars($name) . "</strong>,</p>
            <p style=\"margin:0 0 18px 0;\">Pesanan <strong>" . htmlspecialchars($invoice) . "</strong> sekarang berstatus: <strong style=\"color:#D43E1B;\">" . htmlspecialchars($statusLabel) . "</strong></p>
            {$extra}
            <p style=\"margin:24px 0 0 0;color:#8A7A65;font-size:13px;\">Terima kasih sudah order di Geprek Geh.</p>
        ");
        return self::send($email, $subject, $html);
    }

    /** Shared editorial-style wrapper. */
    private static function wrap(string $heading, string $body): string {
        return '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>'
            . '<body style="margin:0;padding:0;background:#F4ECDC;font-family:\'Plus Jakarta Sans\',system-ui,sans-serif;color:#14110C;">'
            . '<div style="max-width:560px;margin:0 auto;padding:32px 20px;">'
            . '<div style="text-align:center;margin-bottom:28px;">'
            . '<span style="display:inline-block;padding:8px 16px;border:1px solid rgba(20,17,12,0.1);border-radius:999px;font-size:11px;letter-spacing:0.2em;text-transform:uppercase;color:#A82A0E;font-weight:700;">Geprek Geh</span>'
            . '</div>'
            . '<div style="background:#FBF5E7;border:1px solid rgba(20,17,12,0.1);border-radius:24px;padding:32px 28px;box-shadow:0 18px 48px -26px rgba(20,17,12,0.18);">'
            . '<h1 style="font-family:\'Fraunces\',Georgia,serif;font-weight:560;font-size:28px;line-height:1.1;letter-spacing:-0.02em;margin:0 0 20px 0;">' . htmlspecialchars($heading) . '</h1>'
            . $body
            . '</div>'
            . '<p style="text-align:center;color:#8A7A65;font-size:12px;margin-top:24px;">© ' . date('Y') . ' Geprek Geh. Pesan pedas, antar hangat.</p>'
            . '</div></body></html>';
    }
}