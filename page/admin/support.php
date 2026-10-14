<?php
require_once './functions/connection.php';

$user = verif_token($_SESSION["token"]);
$access = $user["type"];
if (!isset($_SESSION["token"]) || empty($_SESSION["token"]) || !$access || $access != "admin") {
    include "./page/error/403.php";
}
else {

handle_support_report($_POST);

$TITLE = "Quotidio Planning - Admin";
?>
<div class="dashboard">
    <div class="dashboard-content">
        <?php include 'components/admin/sidebar.php' ?> 
        <div class="content">
            <div class="ml-5 mt-5">
                <div class="flex">
                    <h1 class="text-2xl">🛟 Support</h1>
                </div>
                <p>Cette page est destiné en cas de problème. (et sert aussi d'informations supplémentaires)</p>
                <p>Si vous avez un problème avec l'outil de réservation, vous pouvez contacter <a href="mailto:contact@quotidio.com">contact@quotidio.com</a> ou utiliser le formulaire si dessous.</p>
                <?php
                render_support_form(array(
                    'email' => isset($user["identifiant"]) ? $user["identifiant"] : '',
                ));
                ?>
                <p>Créer par Yanis Roca--Boyer avec ❤️</p>
                <p>© 2026 Quotidio. Tous droits réservés.</p>
                <br>
            </div>
        </div>
    </div>
</div>
<?php
}
?>