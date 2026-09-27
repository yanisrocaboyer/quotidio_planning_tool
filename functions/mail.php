<?php
require_once 'security.php';

function send_mail($destinary, $subject, $html, $alt = "") {
    global $sql;
    try {
        $request = $sql->prepare("INSERT INTO `mail_queue` (destinary, subject, html, alt) VALUES (:destinary, :subject, :html, :alt)");
        $request->execute([
            "destinary" => $destinary,
            "subject" => $subject,
            "html" => $html,
            "alt" => $alt
        ]);
        return true;
    } catch (\Exception $e) {
        error_log("Erreur lors de la mise en file d'attente de l'email : " . $e->getMessage());
        return false;
    }
}

function handle_support_report($post) {
    if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($post["supportReport"])) {
        return;
    }
    csrf_check();
    $result = submit_support_report($post);
    header("Location: ?supportMsg=" . urlencode($result));
    exit;
}

function submit_support_report($post) {
    $email = htmlspecialchars(trim($post["emailInput"] ?? ""), ENT_QUOTES, "UTF-8");
    $name = htmlspecialchars(trim($post["nameInput"] ?? ""), ENT_QUOTES, "UTF-8");
    $surname = htmlspecialchars(trim($post["surnameInput"] ?? ""), ENT_QUOTES, "UTF-8");
    $subject = htmlspecialchars(trim($post["subjectInput"] ?? ""), ENT_QUOTES, "UTF-8");
    $describe = trim($post["describeInput"] ?? "");

    if (empty($email) || empty($name) || empty($surname) || empty($subject) || empty($describe)) {
        return "error:missing_data";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "error:invalid_email";
    }

    $describe_escaped = htmlspecialchars($describe, ENT_QUOTES, "UTF-8");
    $now = new DateTime();
    $date = $now->format('d/m/Y');
    $hours = $now->format('H:i');

    $report_alt = "Signalement de {$name} {$surname} ({$email})\n"
        . "Envoyé le {$date} à {$hours}\n\n"
        . "Objet du signalement: {$subject}\n"
        . "Description :\n{$describe}";

    $report_html = "<p>Signalement de <b>{$name} {$surname}</b> ({$email})<br>"
        . "Envoyé le {$date} à {$hours}</p>"
        . "<p><b>Objet du signalement:</b> {$subject}</p>"
        . "<p><b>Description :</b><br>" . nl2br($describe_escaped) . "</p>";

    $copy_alt = "Bonjour {$surname},\n\n"
        . "Voici la copie de votre signalement au support de l'organisation des ateliers Quotidio:\n\n"
        . "Objet du signalement : {$subject}\n"
        . "Description :\n{$describe}\n\n"
        . "L'équipe de l'organisation des ateliers Quotidio vous remercie de votre signalement.";

    $copy_html = "<p>Bonjour {$surname},</p>"
        . "<p>Voici la copie de votre signalement au support de l'organisation des ateliers Quotidio:</p>"
        . "<p><b>Objet du signalement :</b> {$subject}</p>"
        . "<p><b>Description :</b><br>" . nl2br($describe_escaped) . "</p>"
        . "<p>L'équipe de l'organisation des ateliers Quotidio vous remercie de votre signalement.</p>";

    $ok = true;
    $ok = send_mail("contact@quotidio.com", "Quotidio Planning - Signalement", $report_html, $report_alt) && $ok;
    $ok = send_mail("yanisrocab@gmail.com", "Quotidio Planning - Signalement", $report_html, $report_alt) && $ok;
    $ok = send_mail($email, "Copie de votre signalement", $copy_html, $copy_alt) && $ok;

    return $ok ? "success" : "error:send_failed";
}

function render_support_form($defaults = array()) {
    $email = isset($defaults['email']) ? htmlspecialchars($defaults['email']) : '';
    $name = isset($defaults['name']) ? htmlspecialchars($defaults['name']) : '';
    $surname = isset($defaults['surname']) ? htmlspecialchars($defaults['surname']) : '';
    ?>
    <div class="mt-6">
        <h2>Signaler un problème</h2>
        <p class="text-gray-600">Votre message sera envoyé à l'équipe de l'organisation des ateliers Quotidio ainsi qu'une copie sur votre adresse email.</p>
        <?php
        if (isset($_GET["supportMsg"])) {
            $msg = $_GET["supportMsg"];
            if ($msg == "success") {
                echo "<p class='text-green-600'>Votre signalement a bien été envoyé !</p>";
            } elseif ($msg == "error:missing_data") {
                echo "<p class='text-red-500'>Veuillez remplir tous les champs du formulaire.</p>";
            } elseif ($msg == "error:invalid_email") {
                echo "<p class='text-red-500'>L'adresse email renseignée n'est pas valide.</p>";
            } else {
                echo "<p class='text-red-500'>Une erreur est survenue lors de l'envoi de votre signalement. Veuillez réessayer.</p>";
            }
        }
        ?>
        <form method="POST" class="mt-2">
            <?= csrf_field() ?>
            <input type="hidden" name="supportReport" value="1">
            <div class="flex flex-col">
                <label for="emailInput">Email</label>
                <input class="input-primary" type="email" id="emailInput" name="emailInput" value="<?= $email ?>" required>
            </div>
            <div class="flex flex-col">
                <label for="nameInput">Nom</label>
                <input class="input-primary" type="text" id="nameInput" name="nameInput" value="<?= $name ?>" required>
            </div>
            <div class="flex flex-col">
                <label for="surnameInput">Prénom</label>
                <input class="input-primary" type="text" id="surnameInput" name="surnameInput" value="<?= $surname ?>" required>
            </div>
            <div class="flex flex-col">
                <label for="subjectInput">Sujet</label>
                <input class="input-primary" type="text" id="subjectInput" name="subjectInput" required>
            </div>
            <div class="flex flex-col">
                <label for="describeInput">Description</label>
                <textarea class="input-primary" id="describeInput" name="describeInput" rows="5" required placeholder="Décrivez le problème, comment le reproduire..."></textarea>
            </div>
            <button type="submit" class="btn-primary">Envoyer</button>
        </form>
    </div>
    <br>
    <?php
}