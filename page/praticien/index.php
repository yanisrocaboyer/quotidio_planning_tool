<?php
require_once './functions/connection.php';
require_once './functions/atelier.php';
require_once './functions/info.php';

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

$TITLE = "Quotidio Planning - Praticien";

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
                            <div class="flex">
                                    <span class="material-symbols-outlined">notes</span>
                                    <p class="mt-auto mb-auto ml-1">
                                        <?php
                                            $clientNote = get_client_note($atelier['client_id']);
                                            if (empty($clientNote) || empty($clientNote['note'])) {
                                                echo "Aucun commentaire";
                                            } else {
                                                echo htmlspecialchars($clientNote["note"]);
                                            }
                                        ?>
                                    </p>
                                </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <h2 class="mt-2" id="pass">Ateliers passés</h2>
                <?php
                $ateliers_pass = get_ateliers("pass", "praticien", $praticien_user['id']);
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
                                        <?php
                                            $clientNote = get_client_note($atelier['client_id']);
                                            if (empty($clientNote) || empty($clientNote['note'])) {
                                                echo "Aucun commentaire";
                                            } else {
                                                echo htmlspecialchars($clientNote["note"]);
                                            }
                                        ?>
                                    </p>
                                </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <h2 class="mt-2" id="candidatures">Mes candidatures</h2>
                <?php
                if (isset($_GET["msg"])) {
                    if ($_GET["msg"] == "candidateAccept") {
                        echo "<p class='text-green-600'>Votre candidature a été enregistré avec succès !</p>";
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
                        <div class="atelier">
                            <div class="ml-2 mt-1 mb-1">
                                <h2>
                                    <?php
                                        $dt = new DateTime($atelier['date_hours']);
                                        echo formatFrenchDate($dt);
                                    ?>
                                </h2>
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
                                        <?php
                                            $clientNote = get_client_note($atelier['client_id']);
                                            if (empty($clientNote) || empty($clientNote['note'])) {
                                                echo "Aucun commentaire";
                                            } else {
                                                echo htmlspecialchars($clientNote["note"]);
                                            }
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <h2 class="mt-2">Ateliers disponibles</h2>
                <?php
                $ateliers_pass = get_ateliers("pending", "praticien");
                ?>
                <div class="grid grid-cols-2 gap-4">
                    <?php if (empty($ateliers_pass)): ?>
                        <p style="grid-column: 1 / -1;">Aucune</p>
                    <?php else: ?>
                        <?php foreach ($ateliers_pass as $atelier): ?>
                        <a href="?action=candidateAtelier&atelier_id=<?= $atelier['id'] ?>">
                        <div class="atelier">
                            <div class="ml-2 mt-1 mb-1">
                                <h2 class="flex">
                                    <?php
                                        $dt = new DateTime($atelier['date_hours']);
                                        echo formatFrenchDate($dt);
                                        info("Cliquer sur l'atelier pour y participer");
                                    ?>
                                </h2>
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
                                        <?php
                                            $clientNote = get_client_note($atelier['client_id']);
                                            if (empty($clientNote) || empty($clientNote['note'])) {
                                                echo "Aucun commentaire";
                                            } else {
                                                echo htmlspecialchars($clientNote["note"]);
                                            }
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        </a>
                        <?php 

                        if (isset($_GET['action']) && $_GET['action'] === 'candidateAtelier' && isset($_GET['atelier_id'])) {
                            $atelier = get_atelier($_GET['atelier_id']);
                            if ($atelier['state'] != "pending") {
                                header("Location: ?msg=errorCandidateAccept#candidatures");
                            } else {
                                add_participation_atelier($atelier['id'], $praticien_user['id']);
                                echo "<script>window.location.href = window.location.pathname + '?id=' + encodeURIComponent(" . $atelier["id"] . ");</script>";
                                header("Location: ?msg=candidateAccept#candidatures");
                            }
                        }

                        endforeach; 
                        
                        ?>
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