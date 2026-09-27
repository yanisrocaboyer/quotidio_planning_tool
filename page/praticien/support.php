<?php
require_once './functions/connection.php';
require_once './functions/atelier.php';

$user = verif_token($_SESSION["token"]);
$access = $user["type"];
if (!isset($_SESSION["token"]) || empty($_SESSION["token"]) || !$access || $access != "praticien") {
    include "./page/error/403.php";
}
else {

$praticien_user = get_praticien($user['user_id']);

handle_support_report($_POST);

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
?>
<div class="dashboard">
    <div class="dashboard-content">
        <?php include 'components/praticien/sidebar.php' ?> 
        <div class="content">
            <div class="ml-5 mt-5">
                <div class="flex">
                    <h1 class="text-2xl">🛟 Support</h1>
                </div>
                <p>Cette page est destiné en cas de problème.</p>
                <p>Si vous avez un problème avec l'outil de réservation, vous pouvez contacter <a href="mailto:contact@quotidio.com">contact@quotidio.com</a> ou utiliser le formulaire si dessous.</p>
                <?php
                render_support_form(array(
                    'email' => isset($praticien_user["email"]) ? $praticien_user["email"] : '',
                    'name' => isset($praticien_user["name"]) ? $praticien_user["name"] : '',
                    'surname' => isset($praticien_user["surname"]) ? $praticien_user["surname"] : '',
                ));
                ?>
                <p>Autres pages:</p>
                <p class="flex"><a class="mr-2" target="_blank" href="/page/help/convention">Convention</a></p>
                <br>
            </div>
        </div>
    </div>
</div>
<?php
}
?>
