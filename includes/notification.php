<?php
/**
 * Email notification helper.
 *
 * Uses PHP mail() for delivery by default. If SMTP settings are configured,
 * it will use direct SMTP delivery instead.
 *
 * Supported environment variables:
 * - EMAIL_FROM
 * - EMAIL_FROM_NAME
 * - SMTP_HOST
 * - SMTP_PORT
 * - SMTP_USERNAME
 * - SMTP_PASSWORD
 * - SMTP_SECURE (ssl, tls, or empty)
 */

function getNotificationConfig() {
    // Gmail SMTP example: replace values with your actual account/app password.
    // If you prefer, set these environment variables instead of hardcoding.
    // SMTP_HOST = smtp.gmail.com
    // SMTP_PORT = 587
    // SMTP_USERNAME = your.email@gmail.com
    // SMTP_PASSWORD = your-app-password
    // SMTP_SECURE = tls
    return [
        'from_email' => getenv('EMAIL_FROM') ?: 'no-reply@houserental.local',
        'from_name' => getenv('EMAIL_FROM_NAME') ?: 'House Rental',
        'smtp_host' => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
        'smtp_port' => getenv('SMTP_PORT') ?: '587',
        'smtp_username' => getenv('SMTP_USERNAME') ?: '',
        'smtp_password' => getenv('SMTP_PASSWORD') ?: '',
        'smtp_secure' => strtolower(getenv('SMTP_SECURE') ?: 'tls'),
    ];
}

function sendEmailNotification($to, $subject, $message) {
    $to = filter_var($to, FILTER_VALIDATE_EMAIL);
    if (!$to) {
        return ['success' => false, 'message' => 'Invalid email address.'];
    }
    if (empty($subject) || empty($message)) {
        return ['success' => false, 'message' => 'Email subject or message is empty.'];
    }

    $config = getNotificationConfig();
    $fromEmail = $config['from_email'];
    $fromName = $config['from_name'];

    $headers = "From: {$fromName} <{$fromEmail}>\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    $htmlMessage = '<html><body>' . nl2br(htmlspecialchars($message)) . '</body></html>';

    if (!empty($config['smtp_host']) && !empty($config['smtp_username']) && !empty($config['smtp_password'])) {
        return sendEmailViaSmtp($to, $subject, $htmlMessage, $fromEmail, $fromName, $config);
    }

    if (!function_exists('mail')) {
        return ['success' => true, 'message' => 'Booking saved. Email notifications are not enabled on this server.'];
    }

    $sent = mail($to, $subject, $htmlMessage, $headers);
    if ($sent) {
        return ['success' => true, 'message' => 'Email sent successfully.'];
    }

    return ['success' => true, 'message' => 'Booking saved. Email notifications are not configured or not available on this server.'];
}

function sendEmailViaSmtp($to, $subject, $message, $fromEmail, $fromName, $config) {
    $host = $config['smtp_host'];
    $port = (int) $config['smtp_port'] ?: 587;
    $secure = $config['smtp_secure'];
    $username = $config['smtp_username'];
    $password = $config['smtp_password'];

    $transportHost = $host;
    if ($secure === 'ssl') {
        $transportHost = 'ssl://' . $host;
    }

    $fp = @fsockopen($transportHost, $port, $errno, $errstr, 10);
    if (!$fp) {
        return ['success' => false, 'message' => "SMTP connection failed: {$errstr} ({$errno})"];
    }

    $response = smtpRead($fp);
    if (strpos($response, '220') !== 0) {
        fclose($fp);
        return ['success' => false, 'message' => 'SMTP greeting failure: ' . trim($response)];
    }

    smtpCmd($fp, "EHLO localhost");
    $response = smtpRead($fp);
    if ($secure === 'tls') {
        smtpCmd($fp, 'STARTTLS');
        $response = smtpRead($fp);
        if (strpos($response, '220') !== 0) {
            fclose($fp);
            return ['success' => false, 'message' => 'SMTP STARTTLS failed: ' . trim($response)];
        }
        stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        smtpCmd($fp, "EHLO localhost");
        smtpRead($fp);
    }

    smtpCmd($fp, 'AUTH LOGIN');
    smtpRead($fp);
    smtpCmd($fp, base64_encode($username));
    smtpRead($fp);
    smtpCmd($fp, base64_encode($password));
    $response = smtpRead($fp);
    if (strpos($response, '235') !== 0) {
        fclose($fp);
        return ['success' => false, 'message' => 'SMTP authentication failed: ' . trim($response)];
    }

    smtpCmd($fp, "MAIL FROM:<{$fromEmail}>");
    smtpRead($fp);
    smtpCmd($fp, "RCPT TO:<{$to}>");
    $response = smtpRead($fp);
    if (strpos($response, '250') !== 0 && strpos($response, '251') !== 0) {
        fclose($fp);
        return ['success' => false, 'message' => 'SMTP recipient rejected: ' . trim($response)];
    }

    smtpCmd($fp, 'DATA');
    smtpRead($fp);

    $headers = [];
    $headers[] = "From: {$fromName} <{$fromEmail}>";
    $headers[] = "To: {$to}";
    $headers[] = "Subject: {$subject}";
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $headers[] = 'Content-Transfer-Encoding: 8bit';

    $data = implode("\r\n", $headers) . "\r\n\r\n" . $message . "\r\n.";
    smtpCmd($fp, $data);
    $response = smtpRead($fp);
    if (strpos($response, '250') !== 0) {
        fclose($fp);
        return ['success' => false, 'message' => 'SMTP send failed: ' . trim($response)];
    }

    smtpCmd($fp, 'QUIT');
    smtpRead($fp);
    fclose($fp);

    return ['success' => true, 'message' => 'Email sent successfully via SMTP.'];
}

function smtpCmd($fp, $cmd) {
    fwrite($fp, $cmd . "\r\n");
}

function smtpRead($fp) {
    $response = "";
    while ($line = fgets($fp, 515)) {
        $response .= $line;
        if (isset($line[3]) && $line[3] === ' ') {
            break;
        }
    }
    return $response;
}
