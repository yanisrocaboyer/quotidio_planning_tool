<?php
require_once __DIR__ . '/../../functions/display.php';
require_once __DIR__ . '/../../functions/atelier.php';
require_once __DIR__ . '/../../functions/connection.php';
require_once __DIR__ . '/../../functions/info.php';

function atelier_card_praticien($atelier, $variant = 'futur') {
    $candidatable = ($variant === 'disponible');
    $candidate = ($variant === 'candidature');

    ob_start();
    ?>
    <?php if ($candidatable): ?>
    <a href="?action=candidateAtelier&atelier_id=<?= $atelier['id'] ?>">
    <?php endif; 
    if ($candidate): ?>
    <a href="?action=delCandidateAtelier&atelier_id=<?= $atelier['id'] ?>">
    <?php endif; ?>
    <div class="atelier <?= $variant ?>">
        <div class="ml-2 mt-1 mb-1">
            <h2 class="<?= ($candidatable || $candidate) ? 'flex' : '' ?>">
                <?php
                    echo format_french_date(new DateTime($atelier['date_hours']));
                    if ($candidatable) {
                        info("Cliquer sur l'atelier pour y participer");
                    }
                    else if ($candidate) {
                        info("Cliquer sur l'atelier pour supprimer votre candidature");
                    }
                ?>
            </h2>
            <?php if ($variant === 'futur'): ?>
            <div class="flex atelier-hidden">
                <span class="material-symbols-outlined">person</span>
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
            <?php else: ?>
            <div class="flex">
                <span class="material-symbols-outlined">location_on</span>
                <p class="mt-auto mb-auto ml-1">
                    <?php
                        $client = get_client($atelier['client_id']);
                        if (isset($client['adress'])) {
                            echo $client['adress'];
                        } else {
                            echo "A définir";
                        }
                    ?>
                </p>
            </div>
            <?php endif; ?>
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
                <span class="material-symbols-outlined">groups_2</span>
                <p class="mt-auto mb-auto ml-1"><?= htmlspecialchars($atelier['max_participants']) ?></p>
            </div>
            <div class="flex atelier-hidden">
                <span class="material-symbols-outlined">notes</span>
                <p class="mt-auto mb-auto ml-1">
                    <?= atelier_client_note($atelier['client_id']) ?>
                </p>
            </div>
        </div>
    </div>
    <?php if ($candidatable || $candidate): ?>
    </a>
    <?php endif; ?>
    <?php
    return ob_get_clean();
}

function atelier_action_candidature($praticien_id) {
    if (!isset($_GET['action']) || $_GET['action'] !== 'candidateAtelier' || !isset($_GET['atelier_id'])) {
        return;
    }

    $atelier = get_atelier($_GET['atelier_id']);
    if (!$atelier || $atelier['state'] != "pending") {
        header("Location: ?msg=errorCandidateAccept#candidatures");
        exit;
    }

    add_participation_atelier($atelier['id'], $praticien_id);
    header("Location: ?msg=candidateAccept#candidatures");
    exit;
}

function atelier_action_delCandidature($praticien_id) {
    if (!isset($_GET['action']) || $_GET['action'] !== 'delCandidateAtelier' || !isset($_GET['atelier_id'])) {
        return;
    }

    $atelier = get_atelier($_GET['atelier_id']);
    if (!$atelier || $atelier['state'] != "pending") {
        header("Location: ?msg=errorCandidateAccept#candidatures");
        exit;
    }

    delete_participation_atelier($atelier['id'], $praticien_id);
    header("Location: ?msg=candidatureDeleted#candidatures");
    exit;
}