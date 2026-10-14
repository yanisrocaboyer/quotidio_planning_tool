<?php
require_once './functions/connection.php';
error_reporting(E_ALL & ~E_WARNING);


$user = verif_token($_SESSION["token"]);
$access = $user["type"];
if (!isset($_SESSION["token"]) || empty($_SESSION["token"]) || !$access || $access != "admin") {
    include "./page/error/403.php";
}
else {

$TITLE = "Quotidio Planning - Admin";

if (isset($_GET["action"]) && $_GET["action"] == "connectAs") {
    login_as($_GET["type"], $_GET["user_id"], $_SESSION["token"]);
    exit;
}

if (!isset($_GET["type"]) || !isset($_GET["user_id"])) {
    ?>
    <div class="dashboard">
    <div class="dashboard-content">
        <?php include 'components/admin/sidebar.php' ?> 
        <div class="content">
            <div class="ml-5 mt-5">
                <p>Type et/ou Utilisateur inconnu</p>
            </div>
        </div>
    </div>
    <?php
} else {
$type = $_GET["type"];
$user_id = $_GET["user_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $result = edit_client($user_id, $_POST);

    if ($result != "error") {
        header("Location: ?type=client&user_id={$user_id}&error=clientUpdateSuccess");
        exit;
    } else {
        header("Location: ?type=client&user_id={$user_id}&error=clientUpdateFailed&msg=". $result);
    }
}

?>
<div class="dashboard">
    <div class="dashboard-content">
        <?php include 'components/admin/sidebar.php' ?> 
        <div class="content">
            <div class="ml-5 mt-5">
                <?php
                if ($type == "praticien"){
                    $praticien = get_praticien($user_id);
                    if ($praticien["id"] == NULL) {
                        echo "<p>Praticien inconnu</p>";
                    } else {
                    $praticien_id = $praticien["id"];
                    ?>

                    <div style="display: none;" id="popupFile" class="popup">
                        <div class="popup-frame relative max-w-[85vw] max-h-[85vh] overflow-auto flex flex-col items-center">
                            <button onclick="closeFile()"
                                    class="absolute top-2 right-4 text-2xl leading-none text-secondary hover:text-red-400">
                                &times;
                            </button>

                            <div id="popupFileContent" class="mt-6"></div>

                            <a id="popupFileDownload" href="#" class="btn-primary mt-5">
                                Télécharger le fichier
                            </a>
                        </div>
                    </div>
                    <h2>Gestion de <?php echo htmlspecialchars($praticien["surname"]) ?> <?php echo htmlspecialchars($praticien["name"]) ?></h2>
                    <?php
                    if (isset($_GET["password"])) {
                        echo "<p class='success'>Le compte utilisateur a bien été créé avec le mot de passe : " . $_GET["password"] ."</p>";
                    }
                    ?>
                    <div class="mt-2">
                        <h3 ><b>Informations :</b></h3>
                        <p>Nom : <?php echo htmlspecialchars($praticien["name"]) ?></p>
                        <p>Prénom : <?php echo htmlspecialchars($praticien["surname"]) ?></p>
                        <p>Adresse e-mail : <?php echo htmlspecialchars($praticien["email"]) ?></p>

                        <p>Numéro de téléphone : <?php echo htmlspecialchars($praticien["tel"]) ?></p>

                        <p>
                            SIRET : <?php echo htmlspecialchars($praticien["siret"]) ?>
                            <a class="ml-2 underline text-secondary"
                               onclick="openFile('<?= base64_encode($praticien['siret_justificatif']) ?>')">
                               Voir le fichier 
                            </a>
                        </p>

                        <p>
                            Assurance : <?php echo htmlspecialchars($praticien["assurance"]); ?>
                            <a class="ml-2 underline text-secondary"
                               onclick="openFile('<?= base64_encode($praticien['assurance_justificatif']) ?>')">
                                Voir le fichier
                            </a>
                        </p>

                        <p>Date d'expiration de l'assurance : <?php echo $praticien["assurance_date"] ?></p>
                        <p>Statut d'adhésion : 
                            <?php 
                                switch ($praticien["state"]) {
                                    case 'pending':
                                        echo "En validation";
                                        break;
                                    case 'deleted':
                                        echo "Supprimé";
                                        break;
                                    case 'validate':
                                        echo "Adhérent";
                                        break;
                                    default:
                                        echo htmlspecialchars($praticien["state"]);
                                }
                            ?>
                        </p>
                    </div>

                    <div class="mt-2">
                        <h3 ><b>Pratiques :</b></h3>
                        <?php
                        $pratiques = get_praticien_justificatif($praticien["id"]);
                        $i = 0;
                        foreach ($pratiques as $pratique) {
                            $i += 1;
                            ?>
                            <div class="mt-2">
                                <p>
                                    Pratique n°<?= $i ?> : <?php echo htmlspecialchars($pratique["pratique"]); ?>
                                    <a class="ml-2 underline text-secondary"
                                    onclick="openFile('<?= base64_encode($pratique['justificatif']) ?>')">
                                        Voir le fichier
                                    </a>
                                </p>
                            </div>
                            <?php
                        }
                        ?>
                    </div>

                    <div class="mt-2">
                        <h3 ><b>Actions :</b></h3>
                        <a href="?action=connectAs&type=praticien&user_id=<?= $praticien["user_id"] ?>">Se connecter en tant que l'utilisateur</a><br>
                        <?php
                        if ($praticien["state"] == "pending") {
                        ?>
                            <a href="?action=valide&type=praticien&user_id=<?= $praticien["id"] ?>">Passer le statut d'adhésion a accepté</a><br>
                            <?php
                            if (isset($_GET['action']) && $_GET['action'] === 'valide' && isset($_GET['user_id']) && $_GET['user_id'] == $praticien["id"]) {
                                require_once($_SERVER['DOCUMENT_ROOT'] . '/functions/connection.php');
                                $result = set_praticien_to_valide($praticien["id"]);
                                echo "<script>window.location.href = window.location.pathname + '?id=' + encodeURIComponent(" . $praticien["id"] . ");</script>";
                                header("Location: ?type=praticien&user_id={$praticien['id']}&password={$result}");
                            }
                            ?>
                       
                            <a href="?action=refuse&type=praticien&user_id=<?= $praticien["id"] ?>" class="warn">Passer le statut d'adhésion a refusé (garder en BDD)</a><br>
                            <?php
                            if (isset($_GET['action']) && $_GET['action'] === 'refuse' && isset($_GET['user_id']) && $_GET['user_id'] == $praticien["id"]) {
                                require_once($_SERVER['DOCUMENT_ROOT'] . '/functions/connection.php');
                                set_praticien_state($praticien["id"], "refuse");
                                echo "<script>window.location.href = window.location.pathname + '?id=' + encodeURIComponent(" . $praticien["id"] . ");</script>";
                                header("Location: ?type=praticien&user_id={$praticien['id']}");
                            }
                            ?>
                        <?php
                        }
                        else if ($praticien["state"] == "validate") {
                        ?>
                            <a href="?action=refuse&type=praticien&user_id=<?= $praticien["id"] ?>" class="warn">Supprimer le compte (garder en BDD)</a><br>
                            <?php
                                if (isset($_GET['action']) && $_GET['action'] === 'refuse' && isset($_GET['user_id']) && $_GET['user_id'] == $praticien["id"]) {
                                    require_once($_SERVER['DOCUMENT_ROOT'] . '/functions/connection.php');
                                    refuse_del_praticien($praticien["id"]);
                                    echo "<script>window.location.href = window.location.pathname + '?id=' + encodeURIComponent(" . $praticien["id"] . ");</script>";
                                    header("Location: ?type=praticien&user_id={$praticien['id']}");
                                }
                        }
                        ?>
                        <a href="?action=delete&type=praticien&user_id=<?= $praticien["id"] ?>" class="danger">Supprimer le compte (supprimer de la BDD)</a>
                        <?php
                            if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['user_id']) && $_GET['user_id'] == $praticien["id"]) {
                                require_once($_SERVER['DOCUMENT_ROOT'] . '/functions/connection.php');
                                delete_praticien($praticien["id"]);
                                echo "<script>window.location.href = window.location.pathname + '?id=' + encodeURIComponent(" . $praticien["id"] . ");</script>";
                                header("Location: praticien_gestion?msg=accountDeleted");
                            }
                        ?>
                    </div>
                    <?php
                }}
                ?>
                <?php
                if ($type == "client"){
                    $client = get_client($user_id);
                    if ($client["id"] == NULL) {
                        echo "<p>Client inconnu</p>";
                    } else {
                    $client_id = $client["id"];
                    ?>
                    <h2>Gestion de <?php echo htmlspecialchars($client["name"]) ?></h2>
                    <div class="mt-2">
                        <?php
                        if (isset($_GET["error"]) && $_GET["error"] == "clientUpdateFailed") {
                            echo "<p class='text-red-500'>Error lors de l'édition du client: {$_GET['msg']} </p>";
                        } else if (isset($_GET["error"]) && $_GET["error"] == "clientUpdateSuccess") {
                            echo "<p class='text-green-600'>Mise à jour du client réussis !</p>";
                        } else if (isset($_GET["infoMsg"])) {
                            echo "<p class='text-blue-500'>{$_GET['infoMsg']}</p>";
                        }
                        ?>
                        <h3 ><b>Informations :</b></h3>
                        <form method="POST">
                            <div class="mt-3">
                                <label for="nameInput">Nom :</label>
                                <input type="text" id="nameInput" name="nameInput" value="<?php echo $client['name'] ?>" required>
                            </div>
                            <div class="mt-3">
                                <label for="emailInput">Adresse e-mail :</label>
                                <input type="email" id="emailInput" name="emailInput" value="<?php echo htmlspecialchars($client['email']) ?>" required>
                            </div>
                            <div class="mt-3">
                                <label for="telInput">Numéro de téléphone :</label>
                                <input type="tel" id="telInput" name="telInput" value="<?php echo htmlspecialchars($client['tel']) ?>" required>
                            </div>
                            <div class="mt-3">
                                <label for="adressInput">Adresse :</label>
                                <input type="text" id="adressInput" name="adressInput" class="w-100" value="<?php echo $client['adress'] ?>" required>
                            </div>
                            <div class="mt-3">
                                <label for="adressInput">Commentaire :</label>
                                <input type="text" id="noteInput" name="noteInput" class="w-100" value="<?php echo $client['note'] ?>">
                            </div>
                            <button class="mt-2 underline" type="submit">Modifier les informations</button>
                        </form>
                    </div>
                    <div class="mt-2">
                        <h3><b>Actions :</b></h3>
                        <?php
                        if($client["user_id"] == NULL) {
                            ?>
                            <p>Aucun compte utilisateur est associé au client:</p>
                            <a href="?action=createClientUser&type=client&user_id=<?= $client["id"] ?>">Créer un compte utilisateur</a>
                            <?php
                                if (isset($_GET['action']) && $_GET['action'] === 'createClientUser' && isset($_GET['user_id']) && $_GET['user_id'] == $client["id"]) {
                                    require_once($_SERVER['DOCUMENT_ROOT'] . '/functions/connection.php');
                                    $result = create_client_user($client["id"]);
                                    echo "<script>window.location.href = window.location.pathname + '?id=' + encodeURIComponent(" . $client["id"] . ");</script>";
                                    header("Location: ?type=client&user_id={$client['id']}&infoMsg={$result}");
                                }
                        } else {
                            ?>
                            <a href="?action=connectAs&type=client&user_id=<?= $client["user_id"] ?>">Se connecter en tant que l'utilisateur</a><br>
                            <a href="?action=deleteClient&type=client&user_id=<?= $client["id"] ?>" class="danger">Supprimer le compte</a>
                            <?php
                                if (isset($_GET['action']) && $_GET['action'] === 'deleteClient' && isset($_GET['user_id']) && $_GET['user_id'] == $client["id"]) {
                                    require_once($_SERVER['DOCUMENT_ROOT'] . '/functions/connection.php');
                                    $result = delete_client($user_id);
                                    echo "<script>window.location.href = window.location.pathname + '?id=' + encodeURIComponent(" . $client["id"] . ");</script>";
                                    header("Location: client_gestion?msg={$result}");
                                }
                        }}
                        ?>
                <?php
                }
                ?>
            </div>
        </div>
    </div>
</div>
<?php
}}
?>

<script>
const popupFile = document.getElementById('popupFile');
const popupFileFrame = popupFile.querySelector('.popup-frame');

function openFile(base64) {
    const container = document.getElementById('popupFileContent');
    const popupFileDownload = document.getElementById('popupFileDownload');
    container.innerHTML = '<p>Chargement...</p>';
    popupFile.style.display = "";

    try {
        if (!base64 || base64.length === 0) {
            throw new Error("Fichier vide");
        }

        const mime = detectMimeType(base64);

        if (mime === 'application/pdf') {
            container.innerHTML = `
                <embed src="data:application/pdf;base64,${base64}" 
                       type="application/pdf" 
                       width="100%" 
                       height="600px" />
            `;
        } else if (mime === 'image/jpeg' || mime === 'image/png' || mime === 'image/webp') {
            container.innerHTML = `
                <img src="data:${mime};base64,${base64}" 
                     style="max-width:100%; height:auto;" 
                     alt="Justificatif" />
            `;
        } else {
            throw new Error("Format non supporté");
        }

        popupFileDownload.onclick = function(e) {
            e.preventDefault();
            downloadFile(base64);
        };

    } catch (e) {
        container.innerHTML = '<p class="text-danger">Erreur lors du chargement du fichier...</p>';
    }
}

function detectMimeType(base64) {
    if (base64.startsWith('JVBERi0')) {
        return 'application/pdf';       
    } else if (base64.startsWith('iVBORw0KGgo')) {
        return 'image/png';            
    } else if (base64.startsWith('/9j/')) {
        return 'image/jpeg';  
    }  else if (base64.startsWith('UklGR')) {
        return 'image/webp';            
    }  else {
        return null;
    }
}

function closeFile() {
    popupFile.style.display = 'none';
    document.getElementById('popupFileContent').innerHTML = '';
    document.getElementById('popupFileDownload').href = '#';
}

function downloadFile(blob) {
    const base64Data = document.getElementById('popupFileContent').querySelector('embed, img')?.src || "";
    if (!base64Data) {
        alert("Aucun fichier à télécharger");
        return;
    }

    let base64, mime, ext;
    if (base64Data.startsWith("data:")) {
        const match = base64Data.match(/^data:(.*);base64,(.*)$/);
        if (match) {
            mime = match[1];
            base64 = match[2];
        }
    } else {
        base64 = base64Data;
        mime = detectMimeType(base64);
    }
    if (!mime) {
        mime = detectMimeType(base64);
    }

    switch (mime) {
        case 'application/pdf':
            ext = 'pdf';
            break;
        case 'image/png':
            ext = 'png';
            break;
        case 'image/jpeg':
            ext = 'jpeg';
            break;
        case 'image/webp':
            ext = 'webp';
            break;
        default:
            ext = '';
            break;
    }

    if (!base64 || !mime || !ext) {
        alert("Impossible de déterminer le type de fichier pour le téléchargement.");
        return;
    }

    const byteCharacters = atob(base64);
    const byteNumbers = new Array(byteCharacters.length);
    for (let i = 0; i < byteCharacters.length; i++) {
        byteNumbers[i] = byteCharacters.charCodeAt(i);
    }
    const byteArray = new Uint8Array(byteNumbers);
    const finalBlob = new Blob([byteArray], { type: mime });
    const url = URL.createObjectURL(finalBlob);

    const a = document.createElement('a');
    a.href = url;
    a.download = `justificatif.${ext}`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);

}

popupFile.addEventListener('click', closeFile);
popupFileFrame.addEventListener('click', (event) => {
    event.stopPropagation();
});
</script>