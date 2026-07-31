<?php
require_once './functions/connection.php';
require_once './functions/atelier.php';

$user = verif_token($_SESSION["token"]);
$access = $user["type"];
if (!isset($_SESSION["token"]) || empty($_SESSION["token"]) || !$access || $access != "client") {
    include "./page/error/403.php";
}
else {

$client_user = get_client($user['user_id']);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $result = add_atelier($_POST);

    if ($result === "success") {
        header("Location: ?error=success");
        exit;
    } else {
        header("Location: .?error=". $result);
    }
}

$TITLE = "Quotidio Planning - Partenaire";

function formatFrenchDate($dt) {
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
?>
<div class="dashboard">
    <div class="dashboard-content">
        <?php include 'components/client/sidebar.php' ?> 
        <div class="content">
            <div class="ml-5 mt-5">
                <div class="flex">
                    <h1 class="text-2xl">👋 Bonjour, <?php echo $client_user["name"] ?></h1>
                </div>
                <h2 class="mt-2" id="futur">Ateliers à venir</h2>
                <?php
                $ateliers_futur = get_ateliers("futur", "client", $client_user['id']);
                ?>
                <div class="grid grid-cols-2 gap-4">
                    <?php foreach ($ateliers_futur as $atelier): ?>
                    <div class="atelier">
                        <div class="ml-2 mt-1 mb-1">
                            <h2>
                                <?php
                                    $dt = new DateTime($atelier['date_hours']);
                                    echo formatFrenchDate($dt);
                                ?>
                            </h2>
                            <div class="flex">
                                <span class="material-symbols-outlined">person</span>
                                <p class="mt-auto mb-auto ml-1">
                                    <?php
                                        echo isset($clients[$atelier['client_id']]) && !empty($clients[$atelier['client_id']]) ? htmlspecialchars($clients[$atelier['client_id']]) : "A définir";
                                    ?>
                                </p>
                            </div>
                            <div class="flex">
                                <span class="material-symbols-outlined">draw</span>
                                <p class="mt-auto mb-auto ml-1"><?= htmlspecialchars($atelier['type']) ?></p>
                            </div>
                            <div class="flex">
                                <span class="material-symbols-outlined">timer</span>
                                <p class="mt-auto mb-auto ml-1">
                                    <?php
                                        echo isset($atelier['duration']) ? htmlspecialchars($atelier['duration']) : "Durée inconnue";
                                    ?>
                                </p>
                            </div>
                            <div class="flex">
                                <span class="material-symbols-outlined">groups_2</span>
                                <p class="mt-auto mb-auto ml-1"><?= htmlspecialchars($atelier['max_participants']) ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <h2 class="mt-2" id="pass">Ateliers passés</h2>
                <?php
                $ateliers_pass = get_ateliers("pass", "client", $client_user['id']);
                ?>
                <div class="grid grid-cols-2 gap-4">
                    <?php foreach ($ateliers_pass as $atelier): ?>
                    <div class="atelier">
                        <div class="ml-2 mt-1 mb-1">
                            <h2>
                                <?php
                                    $dt = new DateTime($atelier['date_hours']);
                                    echo formatFrenchDate($dt);
                                ?>
                            </h2>
                            <div class="flex">
                                <span class="material-symbols-outlined">person</span>
                                <p class="mt-auto mb-auto ml-1">
                                    <?php
                                        echo isset($clients[$atelier['client_id']]) && !empty($clients[$atelier['client_id']]) ? htmlspecialchars($clients[$atelier['client_id']]) : "A définir";
                                    ?>
                                </p>
                            </div>
                            <div class="flex">
                                <span class="material-symbols-outlined">draw</span>
                                <p class="mt-auto mb-auto ml-1"><?= htmlspecialchars($atelier['type']) ?></p>
                            </div>
                            <div class="flex">
                                <span class="material-symbols-outlined">timer</span>
                                <p class="mt-auto mb-auto ml-1">
                                    <?php
                                        echo isset($atelier['duration']) ? htmlspecialchars($atelier['duration']) : "Durée inconnue";
                                    ?>
                                </p>
                            </div>
                            <div class="flex">
                                <span class="material-symbols-outlined">groups_2</span>
                                <p class="mt-auto mb-auto ml-1"><?= htmlspecialchars($atelier['max_participants']) ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
}
?>

<script>
const popupAtelier = document.getElementById("createAtelierPopup");
const createAtelierBtn = document.getElementById("createAtelierBtn");
const popupFrame = document.querySelector(".popup-frame");
const cancelBtn = document.getElementById("cancelBtn");

createAtelierBtn.addEventListener("click", () => {
    popupAtelier.style.display = "";
});
popupAtelier.addEventListener("click", () => {
    popupAtelier.style.display = "none";
});
cancelBtn.addEventListener("click", () => {
    popupAtelier.style.display = "none";
});
popupFrame.addEventListener("click", (event) => {
    event.stopPropagation();
});
</script>