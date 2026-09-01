<?php
require_once __DIR__ . '/atelier.php';

function format_french_date($dt) {
    $days = [
        'Monday'    => 'Lundi',
        'Tuesday'   => 'Mardi',
        'Wednesday' => 'Mercredi',
        'Thursday'  => 'Jeudi',
        'Friday'    => 'Vendredi',
        'Saturday'  => 'Samedi',
        'Sunday'    => 'Dimanche'
    ];
    $months = [
        'January'   => 'Janvier',
        'February'  => 'Février',
        'March'     => 'Mars',
        'April'     => 'Avril',
        'May'       => 'Mai',
        'June'      => 'Juin',
        'July'      => 'Juillet',
        'August'    => 'Août',
        'September' => 'Septembre',
        'October'   => 'Octobre',
        'November'  => 'Novembre',
        'December'  => 'Décembre',
    ];
    $jourEn = $dt->format('l');
    $moisEn = $dt->format('F');
    $jour = isset($days[$jourEn]) ? $days[$jourEn] : $jourEn;
    $mois = isset($months[$moisEn]) ? $months[$moisEn] : $moisEn;
    $jourNum = $dt->format('d');
    $heure = $dt->format('H\hi');
    return ucfirst($jour) . " {$jourNum} " . ucfirst($mois) . " à {$heure}";
}

function atelier_client_note($client_id) {
    $clientNote = get_client_note($client_id);
    if (empty($clientNote) || empty($clientNote['note'])) {
        return "Aucun commentaire";
    }
    return $clientNote["note"];
}
