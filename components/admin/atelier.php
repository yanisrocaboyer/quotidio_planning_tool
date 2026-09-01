<?php
require_once __DIR__ . '/../../functions/display.php';
require_once __DIR__ . '/../../functions/atelier.php';
require_once __DIR__ . '/../../functions/connection.php';
require_once __DIR__ . '/../../functions/info.php';

function atelier_card_admin($atelier, $link = false, $type="pending") {
    ob_start();
    ?>
    <div class="atelier <?= $type ?>" style="position: relative;">
        <?php 
        if($type=="pending" && get_count_participations_atelier($atelier["id"])){
            ?>
            <span class="absolute right-[-12px] top-[-12px] z-10" style="width: 28px; height: 28px; display: inline-block;">
                <span class="absolute inline-flex rounded-full animate-ping bg-orange-500 opacity-75" style="width: 18px; height: 18px; left: 5px; top: 5px;"></span>
                <span class="relative inline-flex rounded-full bg-orange-500" style="width: 18px; height: 18px; left: 5px; top: 5px;"></span>
            </span>
       
            <?php
        }
        ?>
        <div class="ml-2 mt-1 mb-1">
            <h2>
                <?php
                    echo format_french_date(new DateTime($atelier['date_hours']));
                ?>
            </h2>
            <div class="flex">
                <span class="material-symbols-outlined">location_on</span>
                <p class="mt-auto mb-auto ml-1">
                    <?php
                        $client = get_client($atelier['client_id']);
                        if (isset($client['name']) && !empty($client['name'])) {
                            echo $client['name'];
                        } else {
                            echo "A définir";
                        }
                    ?>
                </p>
            </div>
            <div class="flex">
                <span class="material-symbols-outlined">draw</span>
                <p class="mt-auto mb-auto ml-1"><?= $atelier['type'] ?></p>
            </div>
            <div class="flex atelier-hidden">
                <span class="material-symbols-outlined">timer</span>
                <p class="mt-auto mb-auto ml-1">
                    <?php
                        echo isset($atelier['duration']) ? htmlspecialchars($atelier['duration']) : "Durée inconnue";
                    ?>
                </p>
            </div>
            <div class="flex atelier-hidden">
                <span class="material-symbols-outlined">person</span>
                <p class="mt-auto mb-auto ml-1">
                    <?php
                        $praticien = get_praticien($atelier['praticien_id']);
                        if (isset($praticien['name']) && !empty($praticien['name'])) {
                            echo $praticien['name'] . ' ' . $praticien['surname'];
                        } else {
                            echo "A définir";
                        }
                    ?>
                </p>
            </div>
            <div class="flex atelier-hidden">
                <span class="material-symbols-outlined">groups_2</span>
                <p class="mt-auto mb-auto ml-1"><?= htmlspecialchars($atelier['max_participants']) ?> Personnes</p>
            </div>
            <div class="flex atelier-hidden">
                <span class="material-symbols-outlined">notes</span>
                <p class="mt-auto mb-auto ml-1">
                    <?= atelier_client_note($atelier['client_id']) ?>
                </p>
            </div>
        </div>
    </div>
    <?php
    $card = ob_get_clean();

    if ($link) {
        return '<a href="atelier_gestion?id=' . htmlspecialchars($atelier['id']) . '">' . $card . '</a>';
    }
    return $card;
}

function atelier_actions_admin($atelier) {
    ?>
    <div class="mt-2">
        <h3><b>Actions :</b></h3>
        <?php if ($atelier["state"] == 'creation') { ?>
            <a href="?action=setStateOpen&id=<?php echo $atelier['id'] ?>">Ouvrir les candidatures pour les praticiens</a><br>
            <div class="flex">
                <a href="?action=setStateValidate&id=<?php echo $atelier['id'] ?>">Valider l'atelier</a><br>
                <?php
                info("Valider l'atelier vous empêchera à la suite de modifier le type, le nombre de participants max, et les affiliations.");
                ?>
            </div>
        <?php } else if ($atelier["state"] == "pending") { ?>
            <div class="flex">
                <a href="?action=setStateValidate&id=<?php echo $atelier['id'] ?>">Valider l'atelier</a><br>
                <?php
                info("Valider l'atelier vous empêchera à la suite de modifier le type, le nombre de participants max, et les affiliations.");
                ?>
            </div>
        <?php } ?>
        <a href="?action=deleteAtelier&id=<?php echo $atelier['id'] ?>" class="danger">Supprimer l'atelier</a>
    </div>
    <?php
    if (isset($_GET['action']) && isset($_GET['pid']) && isset($_GET['id'])) {
        $id = $_GET['id'];
        $pid = $_GET['pid'];
        if ($_GET['action'] === 'candidateAccept') {
            set_participation_state($pid, 'accept', $id, $_GET["uid"]);
            header("Location: ?id={$id}&msg=candidateAcceptSuccess");
        } elseif ($_GET['action'] === 'candidateRefuse') {
            set_participation_state($pid, 'refuse', $id);
            header("Location: ?id={$id}&msg=candidatureRefuseSuccess");
        }
   
    }
}

function atelier_action_state($atelier) {
    if (!isset($_GET['action']) || !isset($_GET['id']) || intval($_GET['id']) != intval($atelier['id'])) {
        return;
    }

    switch ($_GET['action']) {
        case 'setStateOpen':
            set_state_atelier($atelier['id'], 'pending');
            header("Location: ?id={$atelier['id']}&infoMsg=Candidatures ouvertes");
            exit;
        case 'setStateValidate':
            set_state_atelier($atelier['id'], 'validate');
            header("Location: ?id={$atelier['id']}&infoMsg=Atelier validé");
            exit;
        case 'deleteAtelier':
            del_atelier($atelier['id']);
            header("Location: index?id={$atelier['id']}&infoMsg=Atelier supprimé");
            exit;
        default:
            return;
    }

    exit;
}
