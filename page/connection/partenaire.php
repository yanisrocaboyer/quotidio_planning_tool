<?php
$TITLE = "Quotidio Planning - Connexion";
require_once './functions/connection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $email = isset($_POST['emailInput']) ? trim($_POST['emailInput']) : '';
        $password = isset($_POST['passwordInput']) ? trim($_POST['passwordInput']) : '';

        if (empty($email) || empty($password)) {
            header("Location: ?err=INPUT_NO_FILL");
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: ?err=INVALID_EMAIL_FORMAT");
            exit;
        }

        login($email, $password, "partenaire");
    } catch (Exception $e) {
        echo $e;
    }
}

if (isset($_SESSION["token"])) {
    header("Location: /page/partenaire/");
    exit();
}

?>
<div class="center-box">
    <div class="con-box-1">
        <img class="w-80 ml-auto mr-auto" src="/assets/quotidio_big_logo.png" alt="quotidio_big_logo" draggable="false">
        <div>
            <h1 class="mt-2 text-2xl text-center text-primary">Connexion partenaire</h1>
            <?php
            if (isset($_GET["err"])) {
                if ($_GET["err"] === "INVALID_USER") {
                    ?> <h3 class="text-center text-red-800">L'email ou le mot de passe est incorrect</h3> <?php
                } elseif ($_GET["err"] === "INPUT_NO_FILL") {
                    ?> <h3 class="text-center text-red-800">Tous les champs ne sont pas remplis</h3> <?php
                } elseif ($_GET["err"] === "INVALID_EMAIL_FORMAT") {
                    ?> <h3 class="text-center text-red-800">L'email n'est pas dans le bon format</h3> <?php
                } elseif ($_GET["err"] === "SUCCESS") {
                    ?> <h3 class="text-center text-green-800">Connexion réussis</h3> <?php
                }
            }
            ?>
            <form class="con-form" method="POST">
                <div class="mt-5">
                    <p>Email</p>
                    <input class="input-primary" type="email" name="emailInput" placeholder="johndoe@example.com"/>
                </div>
                <div class="mt-5">
                    <p>Mot de passe</p>
                    <input class="input-primary" type="password" name="passwordInput" placeholder="1234abcd" required/>
                </div>
                <button type="submit" class="btn-primary">Connexion</button>
            </form>
            <p class="text-center">
            </p>
        </div>
    </div>
</div>