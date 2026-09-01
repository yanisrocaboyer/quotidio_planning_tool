<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        echo "<pre>Erreur fatale : " . htmlspecialchars($error['message']) . "\n";
        echo "Fichier : " . htmlspecialchars($error['file']) . " (ligne " . $error['line'] . ")</pre>";
        error_log("FATAL: {$error['message']} in {$error['file']}:{$error['line']}");
    }
});

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    echo "<pre>Erreur [$errno] : " . htmlspecialchars($errstr) . "\n";
    echo "Fichier : " . htmlspecialchars($errfile) . " (ligne $errline)</pre>";
    error_log("Erreur [$errno]: $errstr in $errfile:$errline");
    return true;
});

set_exception_handler(function (\Throwable $e) {
    echo "<pre>Exception non capturée : " . htmlspecialchars($e->getMessage()) . "\n";
    echo htmlspecialchars($e->getTraceAsString()) . "</pre>";
    error_log("Exception non capturée : " . $e->getMessage() . "\n" . $e->getTraceAsString());
});

# ENV
require_once __DIR__ . '/../vendor/autoload.php';

try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
    $dotenv->load();
} catch (\Throwable $e) {
    error_log("Erreur lors du chargement de .env : " . $e->getMessage());
    http_response_code(500);
    echo "Erreur lors du chargement de .env : " . htmlspecialchars($e->getMessage());
    exit("Erreur de configuration du serveur.");
}


# INCLUDE
$sql = new PDO(
    'mysql:host='. $_ENV["HOST"] .':' .$_ENV["PORT"] .';dbname='. $_ENV["DATABASE"] .';charset=utf8',
    $_ENV["USERNAME"],
    $_ENV["PASSWORD"]
);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

function create_mailer() {
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();

        $smtpHost = isset($_ENV["SMTP_SERV"]) ? $_ENV["SMTP_SERV"] : '';
        $smtpPort = isset($_ENV["SMTP_PORT"]) ? (int)$_ENV["SMTP_PORT"] : 465; 

        $mail->Host = $smtpHost;
        $mail->SMTPAuth = true;
        $mail->Username = isset($_ENV["MAIL_USERNAME"]) ? $_ENV["MAIL_USERNAME"] : '';
        $mail->Password = isset($_ENV["MAIL_PASSWORD"]) ? $_ENV["MAIL_PASSWORD"] : '';
        $mail->CharSet = PHPMailer::CHARSET_UTF8;

        if (isset($_ENV["MAIL_SECURE"]) && $_ENV["MAIL_SECURE"] == "0") {
            $mail->SMTPSecure = false;
            $mail->SMTPAutoTLS = false;
        } else {
            if ($smtpPort === 465) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; 
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }
        }

        $mail->Port = $smtpPort;

        $mail->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_SERVER; 
        $mail->Debugoutput = function($str, $level) {
            error_log("PHPMailer Debug [$level]: $str");
        };

        return $mail;

    } catch (PHPMailerException $e) {
        error_log("Erreur lors de la configuration de PHPMailer : " . $e->getMessage());
        return null;
    }
}

function process_mail_queue(): void
{
    global $sql;

    if (!isset($sql) || !$sql instanceof PDO) {
        error_log("Erreur : la connexion PDO (\$sql) n'est pas initialisée.");
        echo "Erreur : connexion à la base de données non initialisée.<br>";
        return;
    }

    try {
        $request = $sql->prepare("SELECT * FROM `mail_queue` WHERE status = 'pending' AND attempts < 3 LIMIT 20");
        $request->execute();
        $emails = $request->fetchAll(PDO::FETCH_ASSOC);
    } catch (\Throwable $e) {
        error_log("Erreur lors de la récupération de la file d'attente des mails : " . $e->getMessage());
        echo "Erreur lors de la récupération de la file d'attente des mails : " . htmlspecialchars($e->getMessage()) . "<br>";
        return;
    }

    if (empty($emails)) {
        echo "Aucun mail en attente.<br>";
        return;
    }

    foreach ($emails as $email) {
        if (empty($email['id']) || empty($email['destinary'])) {
            error_log("Ligne mail_queue invalide (id ou destinary manquant) : " . json_encode($email));
            echo "Ligne invalide dans la file d'attente, ignorée.<br>";
            continue;
        }

        $mail = null;
        try {
            $mail = create_mailer();
        } catch (\Throwable $e) {
            error_log("Exception lors de la création du mailer (queue id {$email['id']}) : " . $e->getMessage());
            echo "Exception lors de la création du mailer (queue id {$email['id']}) : " . htmlspecialchars($e->getMessage()) . "<br>";
        }

        if ($mail === null) {
            echo "Erreur lors de la création du mailer (queue id {$email['id']}).<br>";
        }

        try {
            $mail->setFrom('noreply@quotidio.fr', 'Organisation des ateliers Quotidio');
            $mail->addAddress($email['destinary']);
            $mail->isHTML(true);
            $mail->Subject = $email['subject'] ?? '';
            $mail->Body    = $email['html'] ?? '';
            $mail->AltBody = $email['alt'] ?? '';

            $mail->send();
            $mail->smtpClose();

            try {
                $update = $sql->prepare("UPDATE `mail_queue` SET status = 'sent', sent_at = NOW() WHERE id = :id");
                $update->execute(["id" => $email['id']]);
            } catch (\Throwable $e) {
                error_log("Erreur lors de la mise à jour du statut 'sent' dans la file d'attente des mails (queue id {$email['id']}) : " . $e->getMessage());
                echo "Erreur lors de la mise à jour du statut 'sent' dans la file d'attente des mails (queue id {$email['id']}) : " . htmlspecialchars($e->getMessage()) . "<br>";
            }

        } catch (PHPMailerException $e) {
            error_log("Erreur envoi mail PHPMailer (queue id {$email['id']}) : " . $e->getMessage());
            echo "Erreur envoi mail (queue id {$email['id']}) : " . htmlspecialchars($e->getMessage()) . "<br>";
            update_mail_attempt($sql, $email['id']);
            
        } catch (\Throwable $e) {
            error_log("Erreur envoi mail (queue id {$email['id']}) : " . $e->getMessage());
            echo "Erreur envoi mail (queue id {$email['id']}) : " . htmlspecialchars($e->getMessage()) . "<br>";
            update_mail_attempt($sql, $email['id']);
        }
    }
}

function update_mail_attempt(PDO $sql, int|string $id): void
{
    try {
        $update = $sql->prepare(
            "UPDATE `mail_queue` SET attempts = attempts + 1, status = IF(attempts + 1 >= 10, 'failed', 'pending') WHERE id = :id"
        );
        $update->execute(["id" => $id]);
    } catch (\Throwable $e) {
        error_log("Erreur lors de la mise à jour du statut 'failed/pending' dans la file d'attente des mails (queue id {$id}) : " . $e->getMessage());
        echo "Erreur lors de la mise à jour du statut 'failed/pending' dans la file d'attente des mails (queue id {$id}) : " . htmlspecialchars($e->getMessage()) . "<br>";
    }
}

process_mail_queue();