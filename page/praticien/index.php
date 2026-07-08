<?php
require_once './functions/connection.php';

$user = verif_token($_SESSION["token"]);
$access = $user["type"];
if (!isset($_SESSION["token"]) || empty($_SESSION["token"]) || !$access || $access != "praticien") {
    include "./page/error/403.php";
}
else {

$TITLE = "Quotidio Planning - Praticien.ne";
?>
<div class="center-box">
    <div class="con-box-1">
        <img class="w-80 ml-auto mr-auto" src="/assets/quotidio_big_logo.png" alt="quotidio_big_logo" draggable="false">
        <div>
            <h1 class="text-2xl text-center mt-5">En construction...<h1>
            <h3 class="text-center">Oh.. Cette page n'a pas encore été commencé... Mais pas de panique, ça arrive bientôt !</h3>
        </div>
    </div>
</div>

<?php
}
?>