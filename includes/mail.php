<?php
require_once __DIR__ . '/env.php';
loadEnv(__DIR__ . '/../.env');

require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function mailer($to, $subject, $htmlBody, $altBody = '')
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = $_ENV['MAIL_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['MAIL_USERNAME'];
        $mail->Password   = $_ENV['MAIL_PASSWORD'];
        $mail->SMTPSecure = $_ENV['MAIL_ENCRYPTION'];
        $mail->Port       = $_ENV['MAIL_PORT'];

        $mail->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $altBody ?: strip_tags($htmlBody);

        return $mail->send();
    } catch (Exception $e) {
        error_log('Mailer Error: ' . $mail->ErrorInfo);
        return false;
    }
}

function sendResetEmail($to, $link)
{
    $subject = "Password Reset Request";
    $body = "
        <p>Hello,</p>
        <p>You requested to reset your password. Click the link below to continue:</p>
        <p><a href=\"$link\">Reset Password</a></p>
        <p>This link will expire in 30 minutes. If you didn't request this, ignore this email.</p>
    ";
    return mailer($to, $subject, $body);
}

function sendConfirmationEmail($to)
{
    $subject = "Your Password Was Reset";
    $body = "
        <p>Hello,</p>
        <p>This is a confirmation that your password was changed successfully.</p>
        <p>If you did not do this, please contact support immediately.</p>
    ";
    return mailer($to, $subject, $body);
}

function sendWelcomeEmail($to, $password)
{
    $subject = "Your Land Agency CMS Account";
    $body = "
        <p>Welcome!</p>
        <p>Your CMS account has been created.</p>
        <p><strong>Email:</strong> $to</p>
        <p><strong>Password:</strong> $password</p>
        <p>Please log in and change your password after first login.</p>
        <p><a href='https://ubiquitous-journey-w9jxr6qv4r6c9p7q-8000.app.github.dev/login.php'>Login Here</a></p>
    ";
    return mailer($to, $subject, $body);
}
