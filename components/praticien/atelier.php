<?php
require_once __DIR__ . '/../../functions/display.php';
require_once __DIR__ . '/../../functions/atelier.php';
require_once __DIR__ . '/../../functions/connection.php';
require_once __DIR__ . '/../../functions/info.php';

function atelier_card_praticien($atelier, $variant = 'futur') {
    $candidatable = ($variant === 'disponible');

    ob_start();
    ?>
    <?php if ($candidatable): ?>
    <a href="?action=candidateAtelier&atelier_id=<?= $atelier['id'] ?>">
    <?php endif; ?>
    <div class="atelier">
        <div class="ml-2 mt-1 mb-1">
            <h2 class="<?= $candidatable ? 'flex' : '' ?>">
                <?php
                    echo format_french_date(new DateTime($atelier['date_hours']));
                    if ($candidatable) {
                        info("Cliquer sur l'atelier pour y participer");
                    }
                ?>
            </h2>
            <?php if ($variant === 'futur'): ?>
            <div class="flex">
                <span class="material-symbols-outlined">person</span>
                <p class="mt-auto mb-auto ml-1">
                    <?php
                        $client = get_client($atelier['client_id']);
                        if (isset($client['name']) && !empty($client['name'])) {
                            echo htmlspecialchars($client['name']);
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
                            echo htmlspecialchars($client['adress']);
                        } else {
                            echo "A définir";
                        }
                    ?>
                </p>
            </div>
            <?php endif; ?>
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
            <div class="flex">
                <span class="material-symbols-outlined">notes</span>
                <p class="mt-auto mb-auto ml-1">
                    <?= atelier_client_note($atelier['client_id']) ?>
                </p>
            </div>
        </div>
    </div>
    <?php if ($candidatable): ?>
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
