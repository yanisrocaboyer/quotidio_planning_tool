<?php
require_once __DIR__ . '/../../functions/display.php';
require_once __DIR__ . '/../../functions/atelier.php';
require_once __DIR__ . '/../../functions/connection.php';

function atelier_card_client($atelier) {
    ob_start();
    ?>
    <div class="atelier">
        <div class="ml-2 mt-1 mb-1">
            <h2>
                <?php
                    echo format_french_date(new DateTime($atelier['date_hours']));
                ?>
            </h2>
            <div class="flex">
                <span class="material-symbols-outlined">person</span>
                <p class="mt-auto mb-auto ml-1">
                    <?php
                        $praticien = get_praticien($atelier['praticien_id']);
                        if (isset($praticien['name']) && !empty($praticien['name'])) {
                            echo htmlspecialchars($praticien['name'] . ' ' . $praticien['surname']);
                        } else {
                            echo "A définir";
                        }
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
            <div class="flex">
                <span class="material-symbols-outlined">notes</span>
                <p class="mt-auto mb-auto ml-1">
                    <?= atelier_client_note($atelier['client_id']) ?>
                </p>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
