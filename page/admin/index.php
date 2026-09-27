<?php
require_once './functions/connection.php';
require_once './functions/atelier.php';
require_once './components/admin/atelier.php';


$user = isset($_SESSION["token"]) ? verif_token($_SESSION["token"]) : null;
$access = $user !== null ? $user["type"] : null;
if (!isset($_SESSION["token"]) || empty($_SESSION["token"]) || !$access || $access != "admin") {
    include "./page/error/403.php";
}
else {

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_check();
    $result = add_atelier($_POST);

    if ($result === "success") {
        header("Location: ?error=success");
        exit;
    } else {
        header("Location: .?error=". urlencode($result));
        exit;
    }
}

$TITLE = "Quotidio Planning - Admin";

if (isset($_GET["action"]) && $_GET["action"] == "set_all_creation_atelier_to_pending") {
    if (!isset($_GET["csrf_token"])) {
        http_response_code(403);
        exit("Requête invalide (token de sécurité manquant).");
    }
    csrf_check();
    set_all_creation_atelier_to_pending();
    header("Location: ?info_msg_green=" . urlencode("Les candidatures sont ouvertes pour tous les ateliers !"));
    exit;
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
                            <?= csrf_field() ?>
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
                                <p>Type d'atelier <a href="type_gestion" target="_blank">Gérer les types</a></p>
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

                            <div class="mt-5 flex">
                                <input type="checkbox" name="candidableInput" value="1"/>
                                <p class="ml-2">Ouvrir les candidatures</p>
                            </div>
                            <button type="submit" class="btn-primary">Créer</button>
                        </form>
                        <button class="btn-red" id="cancelBtn">Annuler</button>
                    </div>
                </div>
            </div>
            <div class="ml-5 mt-5">
                <div class="flex">
                    <h1 class="text-2xl">👋 Bonjour, <?php echo h($user["identifiant"]) ?></h1>
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
                if(isset($_GET["info_msg_green"])) {
                    echo "<p class='text-green-900'>" . h($_GET["info_msg_green"]) . "</p>";
                }
                ?>
                <div class="flex flex-wrap items-center mt-2">
                    <h2 class="mr-2" id="pending">Ateliers en cours de gestion</h2>
                    <?php
                    render_atelier_filters('pending', array(
                        'praticiens' => get_praticiens("validate"),
                        'clients' => get_clients(),
                        'types' => get_atelier_types(),
                    ));
                    ?>
                </div>
                <p>Actions rapides:</p>
                <a href="?action=set_all_creation_atelier_to_pending&csrf_token=<?= urlencode(csrf_token()) ?>">Ouvrir les candidatures pour tous les ateliers</a>
                <?php
                $ateliers_pending = get_ateliers("pending");
                ?>
                <div class="grid grid-cols-4">
                    <?php foreach ($ateliers_pending as $atelier): ?>
                        <?php echo atelier_card_admin($atelier, "pending"); ?>
                    <?php endforeach; ?>
                </div>
                <div class="flex flex-wrap items-center mt-2">
                    <h2 class="mr-2" id="futur">Ateliers à venir</h2>
                    <?php
                    render_atelier_filters('futur', array(
                        'praticiens' => get_praticiens("validate"),
                        'clients' => get_clients(),
                        'types' => get_atelier_types(),
                    ));
                    ?>
                </div>
                <?php
                $ateliers_futur = get_ateliers("futur");
                ?>
                <div class="grid grid-cols-4">
                    <?php foreach ($ateliers_futur as $atelier): ?>
                        <?php echo atelier_card_admin($atelier, "futur"); ?>
                    <?php endforeach; ?>
                </div>
                <div class="flex flex-wrap items-center mt-2">
                    <h2 class="mr-2" id="pass">Ateliers passés</h2>
                    <?php
                    render_atelier_filters('pass', array(
                        'praticiens' => get_praticiens("validate"),
                        'clients' => get_clients(),
                        'types' => get_atelier_types(),
                    ));
                    ?>
                </div>
                <?php
                $ateliers_pass = get_ateliers("pass");
                ?>
                <div class="grid grid-cols-4">
                    <?php foreach ($ateliers_pass as $atelier): ?>
                        <?php echo atelier_card_admin($atelier, "pass"); ?>
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