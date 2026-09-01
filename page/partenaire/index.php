<?php
require_once './functions/connection.php';
require_once './functions/atelier.php';
require_once './components/client/atelier.php';

$user = verif_token($_SESSION["token"]);
$access = $user["type"];
if (!isset($_SESSION["token"]) || empty($_SESSION["token"]) || !$access || $access != "client") {
    include "./page/error/403.php";
}
else {

$client_user = get_client($user['user_id']);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $result = add_atelier($_POST);

    if ($result === "success") {
        header("Location: ?error=success");
        exit;
    } else {
        header("Location: .?error=". $result);
    }
}

$TITLE = "Quotidio Planning - Partenaire";
?>
<div class="dashboard">
    <div class="dashboard-content">
        <?php include 'components/client/sidebar.php' ?> 
        <div class="content">
            <div class="ml-5 mt-5">
                <div class="flex">
                    <h1 class="text-2xl">👋 Bonjour, <?php echo $client_user["name"] ?></h1>
                </div>
                <h2 class="mt-2" id="futur">Ateliers à venir</h2>
                <?php
                $ateliers_futur = get_ateliers("futur", "client", $client_user['id']);
                ?>
                <div class="grid grid-cols-4">
                    <?php foreach ($ateliers_futur as $atelier): ?>
                        <?php echo atelier_card_client($atelier, "futur"); ?>
                    <?php endforeach; ?>
                </div>
                <h2 class="mt-2" id="pass">Ateliers passés</h2>
                <?php
                $ateliers_pass = get_ateliers("pass", "client", $client_user['id']);
                ?>
                <div class="grid grid-cols-4">
                    <?php foreach ($ateliers_pass as $atelier): ?>
                        <?php echo atelier_card_client($atelier, "pass"); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
}
?>
