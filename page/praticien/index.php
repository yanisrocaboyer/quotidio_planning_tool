<?php
require_once './functions/connection.php';
require_once './functions/atelier.php';
require_once './components/praticien/atelier.php';

$user = isset($_SESSION["token"]) ? verif_token($_SESSION["token"]) : null;
$access = $user !== null ? $user["type"] : null;
if (!isset($_SESSION["token"]) || empty($_SESSION["token"]) || !$access || $access != "praticien") {
    include "./page/error/403.php";
} else {

$praticien_user = get_praticien($user['user_id']);

$TITLE = "Quotidio Planning - Praticien";

if (isset($_GET['act']) && isset($_GET['atelier_id'])) {
    $atelier_id = $_GET['atelier_id'];
    if ($_GET['act'] === 'candidateAtelier') {
        $participation_exist = get_participations_ap($atelier_id, $praticien_user["id"]);
        $atelier = get_atelier($atelier_id);
        if ($atelier && $atelier['state'] === "pending" && empty($participation_exist)) {
            add_participation_atelier($atelier_id, $praticien_user['id']);
            header("Location: ?msg=candidateAccept#candidatures");
            exit;
        } else {
            header("Location: ?msg=errorCandidateAccept#candidatures");
            exit;
        }
    } else if ($_GET['act'] === 'delCandidateAtelier') {
        $participation_exist = get_participations_ap($atelier_id, $praticien_user["id"]);
        $atelier = get_atelier($atelier_id);
        if ($atelier && $atelier['state'] === "pending" && !empty($participation_exist)) {
            delete_participation_atelier($atelier_id, $praticien_user['id']);
            header("Location: ?msg=candidatureDeleted#candidatures");
            exit;
        } else {
            header("Location: ?msg=errorCandidateAccept#candidatures");
            exit;
        }
    }
}
?>
<div class="dashboard">
    <div class="dashboard-content">
        <?php include 'components/praticien/sidebar.php' ?> 
        <div style="display:none" class="popup" id="candidatureAtelierPopup">
            <div class="popup-frame">
                <div class="popup-content">
                    <h1 class="text-center" id="candidaturePopupTitle" style="margin-top:10px;"></h1>
                    <div class="mt-4 text-center">
                        <p id="candidaturePopupMsg"></p>
                    </div>
                    <div class="mt-6 flex justify-center gap-4" style="margin-top:2rem;gap:1rem;">
                        <button type="button" class="btn-primary" id="candidatureBtnOui">Oui</button>
                        <button type="button" class="btn-primary" id="candidatureBtnNon">Non</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="content">
            <div class="ml-5 mt-5">
                <div class="flex">
                    <h1 class="text-2xl">👋 Bonjour, <?php echo $praticien_user["surname"] ?></h1>
                </div>
                <div class="flex flex-wrap items-center mt-2">
                    <h2 class="mr-2" id="futur">Ateliers à venir</h2>
                    <?php
                    render_atelier_filters('futur', array(
                        'clients' => get_clients(),
                        'types' => get_atelier_types(),
                    ));
                    ?>
                </div>
                <?php
                $ateliers_futur = get_ateliers("futur", "praticien", $praticien_user['id']);
                ?>
                <div class="grid grid-cols-2 ">
                    <?php foreach ($ateliers_futur as $atelier): ?>
                        <?php echo atelier_card_praticien($atelier, 'futur'); ?>
                    <?php endforeach; ?>
                </div>
                <div class="flex flex-wrap items-center mt-2">
                    <h2 class="mr-2" id="pass">Ateliers passés</h2>
                    <?php
                    render_atelier_filters('pass', array(
                        'clients' => get_clients(),
                        'types' => get_atelier_types(),
                    ));
                    ?>
                </div>
                <?php
                $ateliers_pass = get_ateliers("pass", "praticien", $praticien_user['id']);
                ?>
                <div class="grid grid-cols-2">
                    <?php foreach ($ateliers_pass as $atelier): ?>
                        <?php echo atelier_card_praticien($atelier, 'pass'); ?>
                    <?php endforeach; ?>
                </div>
                <div class="flex flex-wrap items-center mt-2">
                    <h2 class="mr-2" id="candidatures">Mes candidatures</h2>
                    <?php
                    render_atelier_filters('candidature', array(
                        'clients' => get_clients(),
                        'types' => get_atelier_types(),
                    ));
                    ?>
                </div>
                <?php
                if (isset($_GET["msg"])) {
                    if ($_GET["msg"] == "candidateAccept") {
                        echo "<p class='text-green-600'>Votre candidature a été enregistré avec succès !</p>";
                    } else if ($_GET["msg"] == "candidatureDeleted") {
                        echo "<p class='text-green-600'>Votre candidature a été supprimée avec succès !</p>";
                    } else if ($_GET["msg"] == "errorCandidateAccept") {
                        echo "<p class='text-red-500'>Oups.. Une erreur est survenue lors de l'enregistrement de votre candidature.</p>";
                    }
                }

                $participations = get_participations_praticien($praticien_user["id"], 4);
                ?>
                <div class="grid grid-cols-4">
                    <?php if ($praticien_user["state"] != "validate"): ?>
                        <p style="grid-column: 1 / -1;" class="text-orange-500">Votre compte est en attente d'adhésion, vous ne pouvez donc pas accédez aux candidatures d'ateliers</p>
                    <?php elseif (empty($participations)): ?>
                        <p style="grid-column: 1 / -1;">Aucune</p>
                    <?php else: ?>
                        <?php foreach ($participations as $participation): 
                        $atelier = get_atelier($participation['atelier_id']);?>
                        <?php echo atelier_card_praticien($atelier, 'candidature'); ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="flex flex-wrap items-center mt-2">
                    <h2 class="mr-2">Ateliers disponibles</h2>
                    <?php
                    render_atelier_filters('disponible', array(
                        'clients' => get_clients(),
                        'types' => get_atelier_types(),
                    ));
                    ?>
                </div>
                <?php
                $ateliers_disponibles = get_ateliers("pending", "praticien");
                ?>
                <div class="grid grid-cols-4">
                    <?php
                    if ($praticien_user["state"] != "validate") {
                        ?>
                        <p style="grid-column: 1 / -1;" class="text-orange-500">Votre compte est en attente d'adhésion, vous ne pouvez donc pas accédez aux candidatures d'ateliers</p>
                        <?php
                    } else {
                    $found = false;
                    if (!empty($ateliers_disponibles)):
                        foreach ($ateliers_disponibles as $atelier):
                            $participation_exist = get_participations_ap($atelier['id'], $praticien_user["id"]);
                            if (!empty($participation_exist)) {
                                continue; 
                            }
                            $found = true;
                            echo atelier_card_praticien($atelier, 'disponible');
                        endforeach;
                    endif;

                    if (!$found):
                    ?>
                        <p style="grid-column: 1 / -1;">Aucun (ou aucun dont vous n'avez pas candidaté)</p>
                    <?php endif;
                    } ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
}
?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const popup = document.getElementById("candidatureAtelierPopup");
    const popupFrame = popup.querySelector(".popup-frame");
    const closeBtn = document.getElementById("closeCandidaturePopup");
    const btnOui = document.getElementById("candidatureBtnOui");
    const btnNon = document.getElementById("candidatureBtnNon");
    let actionUrl = "#";

    document.querySelectorAll('.candidature-trigger').forEach(function(btn){
        btn.addEventListener('click', function(e){
            e.preventDefault();
            e.stopPropagation();
            const atelierId = btn.dataset.atelierId;
            const action = btn.dataset.action; 
            const atelierDate = btn.dataset.atelierDate;
            let msg = "";
            let titre = "";

            if (action === "candidateAtelier") {
                titre = "Candidater à l'atelier ?";
                msg = "Voulez-vous participer à l'atelier du <b>"+atelierDate+"</b> ?";
            } else if (action === "delCandidateAtelier") {
                titre = "Annuler candidature";
                msg = "Voulez-vous <b>supprimer</b> votre candidature pour l'atelier du <b>"+atelierDate+"</b> ?";
            }

            document.getElementById("candidaturePopupTitle").innerText = titre;
            document.getElementById("candidaturePopupMsg").innerHTML = msg;

            actionUrl = "?act=" + encodeURIComponent(action) + "&atelier_id=" + encodeURIComponent(atelierId);

            popup.style.display = "";
        });
    });

    if (btnOui) {
        btnOui.addEventListener('click', function(){
            window.location.href = actionUrl;
        });
    }

    if (btnNon) {
        btnNon.addEventListener('click', function(){
            popup.style.display = "none";
        });
    }
    if (closeBtn) {
        closeBtn.addEventListener('click', function(){
            popup.style.display = "none";
        });
    }
    popup.addEventListener('click', function(){
        popup.style.display = "none";
    });
    popupFrame.addEventListener('click', function(event){
        event.stopPropagation();
    });
});
</script>