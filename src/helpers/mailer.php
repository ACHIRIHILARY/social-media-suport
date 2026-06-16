<?php

require_once __DIR__ . '/../config/app.php';

function sendMail($to, $subject, $message) {
    if (empty($to)) return false;
    
    $headers = [
        'From' => MAIL_FROM_NAME . ' <' . MAIL_FROM . '>',
        'Reply-To' => MAIL_FROM,
        'X-Mailer' => 'PHP/' . phpversion()
    ];
    
    // In local development, mail() might fail if sendmail isn't configured,
    // so we just return true and error_log it.
    if ($_SERVER['SERVER_ADDR'] === '127.0.0.1' || $_SERVER['SERVER_ADDR'] === '::1') {
        error_log("Mock Mail: To: $to | Subject: $subject | Message: $message");
        return true;
    }
    
    return mail($to, $subject, $message, $headers);
}
