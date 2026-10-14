<?php
require_once './functions/connection.php';

handle_support_report($_POST);

$TITLE = "Quotidio Planning - Support";
?>
<div class="dashboard">
    <div class="dashboard-content">
        <?php include 'components/connection/sidebar.php' ?> 
        <div class="content">
            <div class="ml-5 mt-5">
                <div class="flex">
                    <h1 class="text-2xl">🛟 Support</h1>
                </div>
                <p>Cette page est destiné en cas de problème.</p>
                <p>Si vous avez un problème avec l'outil de réservation, vous pouvez contacter <a href="mailto:contact@quotidio.com">contact@quotidio.com</a> ou utiliser le formulaire si dessous.</p>
                <?php
                render_support_form()
                ?>
                <p>Créer par Yanis Roca--Boyer avec ❤️</p>
                <p>© 2026 Quotidio. Tous droits réservés.</p>
                <br>
            </div>
        </div>
    </div>
</div>