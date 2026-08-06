<?php
require_once './functions/connection.php';
require_once './functions/atelier.php';
require_once './components/praticien/atelier.php';

$user = verif_token($_SESSION["token"]);
$access = $user["type"];
if (!isset($_SESSION["token"]) || empty($_SESSION["token"]) || !$access || $access != "praticien") {
    include "./page/error/403.php";
}
else {

$praticien_user = get_praticien($user['user_id']);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $result = add_atelier($_POST);

    if ($result === "success") {
        header("Location: ?error=success");
        exit;
    } else {
        header("Location: .?error=". $result);
    }
}

atelier_action_candidature($praticien_user['id']);
atelier_action_delCandidature($praticien_user['id']);

$TITLE = "Quotidio Planning - Praticien";
?>
<div class="dashboard">
    <div class="dashboard-content">
        <?php include 'components/praticien/sidebar.php' ?> 
        <div class="content">
            <div class="ml-5 mt-5">
                <div class="flex">
                    <h1 class="text-2xl">👋 Bonjour, <?php echo $praticien_user["surname"] ?></h1>
                </div>
                <h2 class="mt-2" id="futur">Ateliers à venir</h2>
                <?php
                $ateliers_futur = get_ateliers("futur", "praticien", $praticien_user['id']);
                ?>
                <div class="grid grid-cols-2 gap-4">
                    <?php foreach ($ateliers_futur as $atelier): ?>
                        <?php echo atelier_card_praticien($atelier, 'futur'); ?>
                    <?php endforeach; ?>
                </div>
                <h2 class="mt-2" id="pass">Ateliers passés</h2>
                <?php
                $ateliers_pass = get_ateliers("pass", "praticien", $praticien_user['id']);
                ?>
                <div class="grid grid-cols-2 gap-4">
                    <?php foreach ($ateliers_pass as $atelier): ?>
                        <?php echo atelier_card_praticien($atelier, 'pass'); ?>
                    <?php endforeach; ?>
                </div>
                <h2 class="mt-2" id="candidatures">Mes candidatures</h2>
                <?php
                if (isset($_GET["msg"])) {
                    if ($_GET["msg"] == "candidateAccept") {
                        echo "<p class='text-green-600'>Votre candidature a été enregistrer avec succès !</p>";
                    } else if ($_GET["msg"] == "candidatureDeleted") {
                        echo "<p class='text-green-600'>Votre candidature a été supprimer avec succès !</p>";
                    } else if ($_GET["msg"] == "errorCandidateAccept") {
                        echo "<p class='text-red-500'>Oups.. Une erreur est survenue lors de l'enrengistrement de votre candidature.</p>";
                    }
                }

                $participations = get_participations_praticien($praticien_user["id"], 4);
                ?>
                <div class="grid grid-cols-2 gap-4">
                    <?php if (empty($participations)): ?>
                        <p style="grid-column: 1 / -1;">Aucune</p>
                    <?php else: ?>
                        <?php foreach ($participations as $partcipation): 
                        $atelier = get_atelier($partcipation['atelier_id']);?>
                        <?php echo atelier_card_praticien($atelier, 'candidature'); ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <h2 class="mt-2">Ateliers disponibles</h2>
                <?php
                $ateliers_disponibles = get_ateliers("pending", "praticien");
                ?>
                <div class="grid grid-cols-2 gap-4">
                    <?php
                    $found = false;
                    if (!empty($ateliers_disponibles)):
                        foreach ($ateliers_disponibles as $atelier):
                            $participation_exist = get_participations_ap($atelier['id'], $praticien_user["id"]);
                            if (!empty($participation_exist)) {
                                continue; 
                            }
                            $found = true;
                    ?>
                        <?php echo atelier_card_praticien($atelier, 'disponible'); ?>
                    <?php
                        endforeach;
                    endif;

                    if (!$found):
                    ?>
                        <p style="grid-column: 1 / -1;">Aucun (ou aucun dont vous n'avez pas candidaté)</p>
                    <?php endif; ?>
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