<?php
require_once './functions/connection.php';

$user = verif_token($_SESSION["token"]);
$access = $user["type"];
if (!isset($_SESSION["token"]) || empty($_SESSION["token"]) || !$access || $access != "admin") {
    include "./page/error/403.php";
}
else {

$TITLE = "Quotidio Planning - Admin";
?>
<div class="dashboard">
    <div class="dashboard-content">
        <?php include 'components/admin/sidebar.php' ?> 
        <div class="content">
            <div class="ml-5 mt-5">
                <div class="flex">
                    <h1 class="text-2xl">Gestions des praticien·ne·s</h1>
                </div>
                <?php
                if (isset($_GET["msg"]) && $_GET["msg"]) {
                    echo "<p class='success'>Compte supprimer avec succès !</p>";
                }
                ?>
                <h2 class="mt-2 mb-2">Praticien·ne·s en attente de validation</h2>
                <?php
                $pending_praticiens = get_praticiens("pending");
                foreach($pending_praticiens as $praticien) {
                    ?>
                    <a href="/page/admin/user_gestion?type=praticien&user_id=<?= $praticien['id'] ?>">
                        <div class="partenaire_card">
                            <div>
                                <img src="/assets/unknown.jpg" alt="Unknown User" class="rounded-md" width="50px" draggable="false">
                            </div>
                            <div class="ml-3 leading-none mt-auto mb-auto">
                                <p class="text-lg/4"><?= $praticien["name"] ?> <?= $praticien["surname"] ?></p>
                                <p class="text-sm/4"><?= $praticien["email"] ?></p>
                            </div>
                        </div>
                    </a>
                    <?php
                }
                if (empty($pending_praticiens)) {
                    echo "<p>Aucun</p>";
                }
                ?>
                <h2 class="mt-2 mb-2">Praticien·ne·s adhérents</h2>
                <?php
                $valide_praticiens = get_praticiens("validate");
                foreach($valide_praticiens as $praticien) {
                    ?>
                    <a href="/page/admin/user_gestion?type=praticien&user_id=<?php echo $praticien['id'] ?>">
                        <div class="partenaire_card">
                            <div>
                                <img src="/assets/unknown.jpg" alt="Unknown User" class="rounded-md" width="50px" draggable="false">
                            </div>
                            <div class="ml-3 leading-none mt-auto mb-auto">
                                <p class="text-lg/4"><?php echo $praticien["name"] ?> <?php echo $praticien["surname"] ?></p>
                                <p class="text-sm/4"><?php echo $praticien["email"] ?></p>
                            </div>
                        </div>
                    </a>
                    <?php
                }
                if (empty($valide_praticiens)) {
                    echo "<p>Aucun</p>";
                }
                ?>
                <h2 class="mt-2 mb-2">Praticien·ne·s refusés/supprimés</h2>
                <?php
                $refuse_praticien = get_praticiens("refuse");
                foreach($refuse_praticien as $praticien) {
                    ?>
                    <a href="/page/admin/user_gestion?type=praticien&user_id=<?php echo $praticien['id'] ?>">
                        <div class="partenaire_card">
                            <div>
                                <img src="/assets/unknown.jpg" alt="Unknown User" class="rounded-md" width="50px" draggable="false">
                            </div>
                            <div class="ml-3 leading-none mt-auto mb-auto">
                                <p class="text-lg/4"><?php echo $praticien["name"] ?> <?php echo $praticien["surname"] ?></p>
                                <p class="text-sm/4"><?php echo $praticien["email"] ?></p>
                            </div>
                        </div>
                    </a>
                    <?php
                }
                if (empty($refuse_praticien)) {
                    echo "<p>Aucun</p>";
                }
                ?>
            </div>
        </div>
    </div>
</div>
<?php
}
?>