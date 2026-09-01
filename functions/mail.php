<?php

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