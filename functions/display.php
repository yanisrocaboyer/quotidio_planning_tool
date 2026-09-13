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

function render_atelier_filters($section, $options = array()) {
    $praticiens = isset($options['praticiens']) ? $options['praticiens'] : array();
    $clients = isset($options['clients']) ? $options['clients'] : array();
    $types = isset($options['types']) ? $options['types'] : array();
    $section = htmlspecialchars($section, ENT_QUOTES, "UTF-8");
    ?>
    <div class="atelier-filters flex flex-wrap gap-2 items-center ml-2">
        <?php if (!empty($praticiens)): ?>
            <select class="input-filter atelier-filter-select" data-section="<?= $section ?>" data-filter="praticien" title="Filtrer par praticien">
                <option value="">Praticien : tous</option>
                <?php foreach ($praticiens as $p): ?>
                    <option value="<?= htmlspecialchars($p['id']) ?>"><?= htmlspecialchars($p['name'] . ' ' . $p['surname']) ?></option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>
        <?php if (!empty($clients)): ?>
            <select class="input-filter atelier-filter-select" data-section="<?= $section ?>" data-filter="client" title="Filtrer par partenaire">
                <option value="">Partenaire : tous</option>
                <?php foreach ($clients as $c): ?>
                    <option value="<?= htmlspecialchars($c['id']) ?>"><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>
        <input type="date" class="input-filter atelier-filter-select" data-section="<?= $section ?>" data-filter="date" title="Filtrer par date">
        <?php if (!empty($types)): ?>
            <select class="input-filter atelier-filter-select" data-section="<?= $section ?>" data-filter="type" title="Filtrer par type d'atelier">
                <option value="">Type : tous</option>
                <?php foreach ($types as $t): ?>
                    <option value="<?= htmlspecialchars($t['name']) ?>"><?= htmlspecialchars($t['name']) ?></option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>
    </div>
    <?php
}
