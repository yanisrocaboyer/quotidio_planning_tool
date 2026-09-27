<?php
require_once './functions/connection.php';
require_once './functions/markdown.php';

$user = isset($_SESSION["token"]) ? verif_token($_SESSION["token"]) : null;
$access = $user !== null ? $user["type"] : null;

if (!isset($_SESSION["token"]) || empty($_SESSION["token"]) || !$user || !$access || !in_array($access, array("admin", "praticien", "client"), true)) {
    include "./page/error/403.php";
}
else {

$TITLE = "Quotidio Planning - Nouveautés";

$valid_types = help_update_types();

$access_to_type = array(
    "admin"     => "admin",
    "praticien" => "praticien",
    "client"    => "partenaire",
);

$role_type = $access_to_type[$access];

$type = isset($_GET["type"]) ? $_GET["type"] : null;
if (!in_array($type, $valid_types, true)) {
    $type = $role_type;
    foreach ($valid_types as $t) {
        if (count(help_update_files($t)) > 0) {
            $type = $t;
            break;
        }
    }
}

if ($access == "praticien"){
    $praticien_user = get_praticien($user['user_id']);
} else if ($access == "client"){
    $client_user = get_client($user['user_id']);
}

$updates = help_update_files($type);
?>
<div class="dashboard">
    <div class="dashboard-content">
        <?php include 'components/' . $access . '/sidebar.php' ?> 
        <div class="content">
            <div class="ml-5 mt-5">
                <div class="flex">
                    <h1 class="text-2xl">Nouveautés</h1>
                </div>
                <p>Cette page référence toutes les dernières mises à jour du site.</p>
                <p>Sélectionnez une version dans la liste ci-dessous pour consulter le détail des nouveautés apportées.</p>

                <div class="mt-4">
                    <?php if (empty($updates)): ?>
                        <p>Aucune mise à jour disponible.</p>
                    <?php else: ?>
                        <ul class="mt-2 space-y-2">
                            <?php foreach ($updates as $update): ?>
                                <li>
                                    <a href="/help/update?type=<?= urlencode($type) ?>&ver=<?= urlencode($update['ver']) ?>"
                                       class="underline text-secondary">
                                        <?= htmlspecialchars($update['name']) ?> (v<?= htmlspecialchars($update['ver']) ?>)
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
}
?>
