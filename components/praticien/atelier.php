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
    <div class="atelier atelier-clickable atelier-filter-item <?= $variant ?>"
        data-filter-section="<?= htmlspecialchars($variant) ?>"
        data-praticien="<?= isset($atelier['praticien_id']) ? htmlspecialchars($atelier['praticien_id']) : '' ?>"
        data-client="<?= isset($atelier['client_id']) ? htmlspecialchars($atelier['client_id']) : '' ?>"
        data-date="<?= htmlspecialchars(date('Y-m-d', strtotime($atelier['date_hours']))) ?>"
        data-type="<?= isset($atelier['type']) ? htmlspecialchars($atelier['type']) : '' ?>">
        <div class="ml-2 mt-1 mb-1">
            <h2 class="<?= ($candidatable || $candidate) ? 'flex' : '' ?>">
                <?php
                    echo format_french_date(new DateTime($atelier['date_hours']));
                    if ($candidatable) {
                        info("Cliquer sur l'atelier pour voir les détails");
                    }
                    else if ($candidate) {
                        info("Cliquer sur l'atelier pour voir les détails");
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
            <?php if ($candidatable): ?>
            <div class="atelier-actions">
                <button type="button"
                    class="w-full text-center border rounded-4xl mt-2 border-black p-1 candidature-trigger"
                    data-atelier-id="<?= $atelier['id'] ?>"
                    data-action="candidateAtelier"
                    data-atelier-date="<?= htmlspecialchars(format_french_date(new DateTime($atelier['date_hours']))) ?>">
                    Participer à l'atelier
                </button>
            </div>
            <?php elseif ($candidate): ?>
            <div class="atelier-actions">
                <button type="button"
                    class="w-full text-center border rounded-4xl mt-2 border-black p-1 candidature-trigger"
                    data-atelier-id="<?= $atelier['id'] ?>"
                    data-action="delCandidateAtelier"
                    data-atelier-date="<?= htmlspecialchars(format_french_date(new DateTime($atelier['date_hours']))) ?>">
                    Supprimer ma candidature
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
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