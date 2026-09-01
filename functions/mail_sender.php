<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

function create_mailer() {
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $smtpHost = isset($_ENV["SMTP_SERV"]) ? $_ENV["SMTP_SERV"] : '';
        $smtpPort = isset($_ENV["SMTP_PORT"]) ? $_ENV["SMTP_PORT"] : 587;
        $mail->Host = $smtpHost;
        $mail->SMTPAuth = true;
        $mail->Username = isset($_ENV["MAIL_USERNAME"]) ? $_ENV["MAIL_USERNAME"] : '';
        $mail->Password = isset($_ENV["MAIL_PASSWORD"]) ? $_ENV["MAIL_PASSWORD"] : '';
        $mail->CharSet = PHPMailer::CHARSET_UTF8;
        if (isset($_ENV["MAIL_SECURE"]) && $_ENV["MAIL_SECURE"] == "0") {
            $mail->SMTPSecure = false;
            $mail->SMTPAutoTLS = false;
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }
        $mail->Port = $smtpPort;
        return $mail;
    } catch (MailException $e) {
        error_log("Erreur lors de la configuration de PHPMailer : " . $e->getMessage());
        return null;
    }
}