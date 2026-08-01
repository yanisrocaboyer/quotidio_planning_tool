<?php
require_once './functions/connection.php';
require_once './functions/atelier.php';


$user = verif_token($_SESSION["token"]);
$access = $user["type"];
if (!isset($_SESSION["token"]) || empty($_SESSION["token"]) || !$access || $access != "admin") {
    include "./page/error/403.php";
}
else {

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $result = add_atelier($_POST);

    if ($result === "success") {
        header("Location: ?error=success");
        exit;
    } else {
        header("Location: .?error=". $result);
    }
}

$TITLE = "Quotidio Planning - Admin";

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
        <?php include 'components/admin/sidebar.php' ?> 
        <div class="content">
            <div style="display: none" class="popup" id="createAtelierPopup">
                <div class="popup-frame">
                    <div class="popup-content">
                        <h1 class="text-center">Créer un atelier</h1>
                        <form method="POST">
                            <div class="mt-2 flex flex-col md:flex-row gap-4 w-full">
                                <div class="flex flex-col w-full md:w-1/2"">
                                    <p>Date</p>
                                    <input class="input-primary-small" type="date" name="dateInput" placeholder="JJ/MM/AAAA" required/>
                                </div>
                                <div class="flex flex-col w-full md:w-1/2"">
                                    <p>Heure</p>
                                    <input class="input-primary-small" type="time" name="hourInput" placeholder="HH:MM" required/>
                                </div>
                            </div>
                            <div class="mt-5">
                                <p>Type d'atelier</p>
                                <?php
                                    $atelier_types = get_atelier_types();
                                ?>
                                <select class="input-primary" name="typeInput" required>
                                    <option value="">Sélectionner un type d'atelier</option>
                                    <?php foreach ($atelier_types as $type): ?>
                                        <option value="<?= htmlspecialchars($type['name']) ?>">
                                            <?= htmlspecialchars($type['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                       
                            <div class="mt-5">
                                <p>Partenaire</p>
                                <?php
                                    $clients = get_clients();
                                ?>
                                <select class="input-primary" name="clientInput" required>
                                    <option value="">Sélectionner un partenaire</option>
                                    <?php foreach ($clients as $client): ?>
                                        <option value="<?= htmlspecialchars($client['id']) ?>">
                                            <?= htmlspecialchars($client['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                       
                            <div class="mt-2 flex flex-col md:flex-row gap-4 w-full">
                                <div class="mt-5">
                                    <p>Durée</p>
                                    <input class="input-primary-small" type="time" name="timeInput" placeholder="johndoe@example.com" required/>
                                </div>
                                <div class="mt-5">
                                    <p>Max. Personnes</p>
                                    <input class="input-primary-small" type="num" name="maxPersonInput" placeholder="12" required/>
                                </div>
                            </div>
                            <button type="submit" class="btn-primary">Créer</button>
                        </form>
                        <button class="btn-red" id="cancelBtn">Annuler</button>
                    </div>
                </div>
            </div>
            <div class="ml-5 mt-5">
                <div class="flex">
                    <h1 class="text-2xl">👋 Bonjour, <?php echo $user["identifiant"] ?></h1>
                    <button class="ml-auto mr-5 bg-black text-white p-2 rounded-4xl" id="createAtelierBtn"><span class="material-symbols-outlined">add</span></button>
                </div>
                <?php
                if(isset($_GET["error"])){
                    if ($_GET["error"] == "success"){
                    ?>
                    <p class="text-green-900">Création de l'atelier avec succès !</p>
                    <?php                    
                    }
                    else {
                    ?>
                    <p class="text-red-500">Erreur lors de la création de l'atelier</p>
                    <?php                   
                    }
                }
                ?>
                <h2 class="mt-2" id="pending">Ateliers en cours de gestion</h2>
                <?php
                $clients = [];
                foreach(get_clients() as $c) {
                    $clients[$c['id']] = $c['name'];
                }

                $ateliers_pending = get_ateliers("pending");
                ?>
                <div class="grid grid-cols-2 gap-4">
                    <?php foreach ($ateliers_pending as $atelier): ?>
                    <a href="atelier_gestion?id=<?= $atelier['id'] ?>"><div class="atelier">
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
                                <p class="mt-auto mb-auto ml-1"><?= htmlspecialchars($atelier['max_participants'])?> Personnes</p>
                            </div>
                        </div>
                    </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <h2 class="mt-2" id="futur">Ateliers à venir</h2>
                <?php
                $ateliers_futur = get_ateliers("futur");
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
                                <p class="mt-auto mb-auto ml-1"><?= htmlspecialchars($atelier['max_participants']) ?> Personnes</p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <h2 class="mt-2" id="pass">Ateliers passés</h2>
                <?php
                $ateliers_pass = get_ateliers("pass");
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
                                <p class="mt-auto mb-auto ml-1"><?= htmlspecialchars($atelier['max_participants']) ?> Personnes</p>
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

popupAtelier.addEventListener("click", () => {
    popupAtelier.style.display = "none";
});
if (createAtelierBtn) {
    createAtelierBtn.addEventListener("click", (e) => {
        e.preventDefault();
        popupAtelier.style.display = "";
    });
}
if (cancelBtn) {
    cancelBtn.addEventListener("click", () => {
        popupAtelier.style.display = "none";
    });
}
popupAtelier.querySelector(".popup-frame").addEventListener("click", (event) => {
    event.stopPropagation();
});
</script>