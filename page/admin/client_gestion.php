<?php
require_once './functions/connection.php';

$user = verif_token($_SESSION["token"]);
$access = $user["type"];
if (!isset($_SESSION["token"]) || empty($_SESSION["token"]) || !$access || $access != "admin") {
    include "./page/error/403.php";
} else {

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $result = add_client($_POST);

    if ($result != "error") {
        header("Location: ?error=success&password={$result}");
        exit;
    } else {
        header("Location: ?error=". $result);
    }
}

$TITLE = "Quotidio Planning - Admin";
?>
<div class="dashboard">
    <div class="dashboard-content">
        <?php include 'components/admin/sidebar.php' ?> 
        <div class="content">
            <div style="display: none" class="popup" id="createClientPopup">
                <div class="popup-frame">
                    <div class="popup-content">
                        <h1 class="text-center">Ajouter un partenaire</h1>
                        <form method="POST">
                            <div class="mt-5">
                                <p>Nom</p>
                                <input class="input-primary" type="text" name="nameInput" placeholder="Nom du client" required/>
                            </div>
                            <div class="mt-5">
                                <p>Adresse</p>
                                <input class="input-primary" type="text" name="adressInput" placeholder="Adresse du client" required/>
                            </div>
                            <div class="mt-5">
                                <p>Email</p>
                                <input class="input-primary" type="email" name="emailInput" placeholder="Email du client" required/>
                            </div>
                            <div class="mt-5">
                                <p>Tel</p>
                                <input class="input-primary" type="tel" name="telInput" placeholder="Téléphone du client" required/>
                            </div>
                            <button type="submit" class="btn-primary">Créer</button>
                        </form>
                        <button class="btn-red" id="cancelBtn">Annuler</button>
                    </div>
                </div>
            </div>
            <div class="ml-5 mt-5">
                <div class="flex">
                    <h1 class="text-2xl">Gestions des partenaires</h1>
                    <button class="ml-auto mr-5 bg-black text-white p-2 rounded-4xl" id="createClientBtn"><span class="material-symbols-outlined">add</span></button>
                </div>
                <?php
                if (isset($_GET["msg"]) && $_GET["msg"] == "accountDeleted") {
                    echo "<p class='success'>Compte supprimer avec succès !</p>";
                } else if (isset($_GET["msg"])) {
                    echo "<p>{$_GET['msg']}</p>";
                }
                ?>
                <?php
                if(isset($_GET["error"])){
                    if ($_GET["error"] == "success"){
                    if ($_GET["password"] != "error:internal" || $_GET["password"] != "error:user_creation_failed" || $_GET["password"] != "error:invalid_email"){
                    ?>
                    <p class="text-green-900">Création du partenaire avec succès !</p>
                    <p class="text-green-900">Mot de passe du partenaire: <?= $_GET["password"] ?></p>
                    <?php      
                    } else {
                        ?>
                        <p class="text-red-500">Erros lors de la création du compte partenaire !</p>
                        <p class="text-red-500">Type d'erreur: <?= $_GET["password"] ?></p>
                        <?php
                    }          
                    }
                    else {
                    ?>
                    <p class="text-red-500">Erreur lors de la création du client</p>
                    <?php                   
                    }
                }
                ?>
                <h2 class="mt-2 mb-2">Liste des partenaires</h2>
                <?php
                $clients = get_clients();
                if (!empty($clients)) {
                    foreach($clients as $client) {
                        ?>
                        <a href="/page/admin/user_gestion?type=client&user_id=<?php echo $client['id'] ?>">
                            <div class="partenaire_card">
                                <div>
                                    <img src="/assets/unknown.jpg" alt="Unknown User" class="rounded-md" width="50px" draggable="false">
                                </div>
                                <div class="ml-3 leading-none mt-auto mb-auto">
                                    <p class="text-lg/4"><?php echo htmlspecialchars($client["name"]) ?> </p>
                                    <p class="text-sm/4"><?php echo htmlspecialchars($client["email"]) ?></p>
                                </div>
                            </div>
                        </a>
                        <?php
                    }
                } else {
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

<script>
const popupClient = document.getElementById("createClientPopup");
const createClientBtn = document.getElementById("createClientBtn");
const cancelBtn = document.getElementById("cancelBtn");

popupClient.addEventListener("click", () => {
    popupClient.style.display = "none";
});
if (createClientBtn) {
    createClientBtn.addEventListener("click", (e) => {
        e.preventDefault();
        popupClient.style.display = "";
    });
}
if (cancelBtn) {
    cancelBtn.addEventListener("click", () => {
        popupClient.style.display = "none";
    });
}
popupClient.querySelector(".popup-frame").addEventListener("click", (event) => {
    event.stopPropagation();
});
</script>