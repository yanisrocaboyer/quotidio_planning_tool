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
    <div class="side-bar">
        <div class="m-1 p-2 flex">
            <div>
                <img src="/assets/unknown.jpg" alt="Unknown User" class="rounded-md" width="50px">
            </div>
            <div class="ml-3 leading-none flex flex-col justify-center">
                <h1>Admin</h1>
                <h1 class="text-gray-500 text-xs"><?php echo $user["identifiant"] ?></h1>
            </div>
        </div>
        <hr class="text-gray-200">
    </div>
    <div class="content ">
        <div class="ml-10 mt-5">
            <h1 class="text-2xl">👋 Bonjour, <?php echo $user["identifiant"] ?></h1>
        </div>
    </div>
</div>
<?php
}
?>