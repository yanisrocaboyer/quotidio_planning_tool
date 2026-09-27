<?php
require_once './functions/connection.php';

$user = verif_token($_SESSION["token"]);
$access = $user["type"];
if (!isset($_SESSION["token"]) || empty($_SESSION["token"]) || !$access || $access != "praticien") {
    include "./page/error/403.php";
} else {

    $praticien_user = get_praticien($user['user_id']);

    function render_account_msg($msg) {
        if (empty($msg)) {
            return;
        }
        switch ($msg) {
            case "success":
                echo "<p class='text-green-600'>Vos informations ont bien été mises à jour. Votre compte repasse en attente d'adhésion jusqu'à validation.</p>";
                break;
            case "success_pwd":
                echo "<p class='text-green-600'>Votre mot de passe a été modifié avec succès.</p>";
                break;
            case "error:missing_data":
                echo "<p class='text-red-500'>Veuillez remplir tous les champs obligatoires.</p>";
                break;
            case "error:invalid_email":
                echo "<p class='text-red-500'>L'adresse email renseignée n'est pas valide.</p>";
                break;
            case "error:email_exists":
                echo "<p class='text-red-500'>Cette adresse email est déjà utilisée par un autre praticien.</p>";
                break;
            case "error:invalid_file":
                echo "<p class='text-red-500'>Fichier invalide. Formats acceptés : JPEG, PNG, PDF.</p>";
                break;
            case "error:not_found":
                echo "<p class='text-red-500'>Élément introuvable.</p>";
                break;
            case "error:password_wrong":
                echo "<p class='text-red-500'>Votre mot de passe actuel est incorrect.</p>";
                break;
            case "error:password_nomatch":
                echo "<p class='text-red-500'>La confirmation ne correspond pas au nouveau mot de passe.</p>";
                break;
            case "error:password_minlength":
                echo "<p class='text-red-500'>Le nouveau mot de passe doit comporter au moins 8 caractères.</p>";
                break;
            default:
                echo "<p class='text-red-500'>Une erreur est survenue : " . htmlspecialchars($msg) . "</p>";
                break;
        }
    }

    handle_support_report($_POST);

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (isset($_POST["accountInfo"])) {
            $result = update_praticien_info($praticien_user["user_id"], $_POST);
            header("Location: ?msg=" . urlencode($result));
            exit;
        }

        if (isset($_POST["changePassword"])) {
            $current_pwd = $_POST['current_password'] ?? '';
            $new_pwd = $_POST['new_password'] ?? '';
            $confirm_pwd = $_POST['confirm_password'] ?? '';

            if (empty($current_pwd) || empty($new_pwd) || empty($confirm_pwd)) {
                $result = "error:missing_data";
            } elseif ($new_pwd !== $confirm_pwd) {
                $result = "error:password_nomatch";
            } elseif (strlen($new_pwd) < 8) {
                $result = "error:password_minlength";
            } else {
                $userData = get_user_by_id($praticien_user["user_id"]);
                if (!$userData || empty($userData["password"])) {
                    $result = "error:not_found";
                } elseif (!password_verify($current_pwd, $userData["password"])) {
                    $result = "error:password_wrong";
                } else {
                    $result = update_user_password($praticien_user["user_id"], $new_pwd);
                }
            }
            header("Location: ?msg=" . urlencode($result));
            exit;
        }

        if (isset($_POST["updateSiretJustif"])) {
            $result = update_praticien_justificatif($praticien_user["id"], "siret", $_FILES["siret_file"] ?? null);
            header("Location: ?msg=" . urlencode($result));
            exit;
        }

        if (isset($_POST["updateAssuranceJustif"])) {
            $result = update_praticien_justificatif($praticien_user["id"], "assurance", $_FILES["assurance_file"] ?? null);
            header("Location: ?msg=" . urlencode($result));
            exit;
        }

        if (isset($_POST["addPratique"])) {
            $result = add_pratique_justificatif($praticien_user["id"], $_POST["pratique_name"] ?? "", $_FILES["pratique_file"] ?? null);
            header("Location: ?msg=" . urlencode($result));
            exit;
        }

        if (isset($_POST["updatePratiqueJustif"])) {
            $result = update_pratique_justificatif($praticien_user["id"], $_POST["justif_id"] ?? null, $_FILES["pratique_file"] ?? null);
            header("Location: ?msg=" . urlencode($result));
            exit;
        }

        if (isset($_POST["removePratique"])) {
            $result = remove_pratique_justificatif($praticien_user["id"], $_POST["justif_id"] ?? null);
            header("Location: ?msg=" . urlencode($result));
            exit;
        }
    }

    $TITLE = "Quotidio Planning - Praticien";

    $pratiques = get_praticien_justificatif($praticien_user["id"]);
?>
<div class="dashboard">
    <div class="dashboard-content">
        <?php include 'components/praticien/sidebar.php' ?> 
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
        <div style="display: none;" id="accountModifPopup" class="popup">
            <div class="popup-frame">
                <div class="popup-content">
                    <h1 class="text-center" style="margin-top:10px;">Êtes-vous sûr ?</h1>
                    <div class="mt-4 text-center">
                        <p>Êtes vous sûr de vouloir modifier ces informations, votre compte repassera en <b>statut d'attente d'adhésion</b> jusqu'à validation, cela veut donc dire que vous n'aurez pas accès aux candidatures d'ateliers.</p>
                        <p class="mt-2 text-red-500">Veillez prévenir au préalable avant la modification de ces informations.</p>
                    </div>
                    <div class="mt-6 flex justify-center gap-4" style="margin-top:2rem;gap:1rem;">
                        <button type="button" class="btn-primary" id="accountModifBtnOui">Oui</button>
                        <button type="button" class="btn-primary" id="accountModifBtnNon">Non</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="content">
            <div class="ml-5 mt-5">
                <div class="flex">
                    <h1 class="text-2xl flex items-center">
                        <span class="material-symbols-outlined mr-2" style="font-size: 2rem;">account_circle</span>
                        Mon compte
                    </h1>
                </div>
                <div class="mt-1">
                    Statut d'adhésion :
                    <?php
                    switch ($praticien_user["state"]) {
                        case 'validate':
                            echo "<span class='text-green-600'>Adhérent</span>";
                            break;
                        case 'pending':
                            echo "<span class='text-orange-500'>En attente d'adhésion</span>";
                            break;
                        case 'refuse':
                            echo "<span class='text-red-500'>Refusé</span>";
                            break;
                        default:
                            echo htmlspecialchars($praticien_user["state"]);
                    }
                    ?>
                </div>
                <?php render_account_msg($_GET["msg"] ?? ""); ?>

                <div class="mt-4">
                    <h2><b>Informations :</b></h2>
                    <p class="text-gray-500 text-sm">Le nom et le prénom ne sont pas modifiables.</p>
                    <form method="POST" enctype="multipart/form-data" data-confirm="1" class="mt-2">
                        <input type="hidden" name="accountInfo" value="1">
                        <div class="flex flex-col">
                            <label for="name">Nom</label>
                            <input class="input-primary" type="text" id="name" value="<?php echo htmlspecialchars($praticien_user["name"]) ?>" disabled>
                        </div>
                        <div class="flex flex-col">
                            <label for="surname">Prénom</label>
                            <input class="input-primary" type="text" id="surname" value="<?php echo htmlspecialchars($praticien_user["surname"]) ?>" disabled>
                        </div>
                        <div class="flex flex-col">
                            <label for="email">Adresse e-mail (la modification de l'e-mail ne modifie pas l'identifiant)</label>
                            <input class="input-primary" type="email" id="email" name="email" value="<?php echo htmlspecialchars($praticien_user["email"]) ?>" required>
                        </div>
                        <div class="flex flex-col">
                            <label for="tel">Numéro de téléphone</label>
                            <input class="input-primary" type="tel" id="tel" name="tel" value="<?php echo htmlspecialchars($praticien_user["tel"]) ?>" required>
                        </div>
                        <div class="flex flex-col">
                            <label for="siret">SIRET</label>
                            <input class="input-primary" type="text" id="siret" name="siret" value="<?php echo htmlspecialchars($praticien_user["siret"]) ?>" required>
                        </div>
                        <div class="flex flex-col">
                            <label for="assurance">Assurance</label>
                            <input class="input-primary" type="text" id="assurance" name="assurance" value="<?php echo htmlspecialchars($praticien_user["assurance"]) ?>" required>
                        </div>
                        <div class="flex flex-col">
                            <label for="assurance_date">Date d'expiration de l'assurance</label>
                            <input class="input-primary" type="date" id="assurance_date" name="assurance_date" value="<?php echo htmlspecialchars($praticien_user["assurance_date"]) ?>">
                        </div>
                        <button type="submit" class="btn-primary">Enregistrer les modifications</button>
                    </form>

                    <div class="mt-8">
                        <h3><b>Modifier le mot de passe</b></h3>
                        <form method="POST" class="mt-2 max-w-md">
                            <input type="hidden" name="changePassword" value="1">
                            <div class="flex flex-col mt-2">
                                <label for="current_password">Mot de passe actuel</label>
                                <input class="input-primary" type="password" id="current_password" name="current_password" required autocomplete="current-password">
                            </div>
                            <div class="flex flex-col mt-2">
                                <label for="new_password">Nouveau mot de passe</label>
                                <input class="input-primary" type="password" id="new_password" name="new_password" required autocomplete="new-password" minlength="8">
                                <span class="text-xs text-gray-500 mt-1">Au moins 8 caractères</span>
                            </div>
                            <div class="flex flex-col mt-2">
                                <label for="confirm_password">Confirmation du nouveau mot de passe</label>
                                <input class="input-primary" type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password">
                            </div>
                            <button type="submit" class="btn-primary mt-2">Changer le mot de passe</button>
                        </form>
                    </div>
                </div>

                <div class="mt-6">
                    <h2><b>Pratiques :</b></h2>
                    <?php if ($pratiques && count($pratiques) > 0): ?>
                        <?php $i = 0; foreach ($pratiques as $pratique): $i++; ?>
                            <div class="mt-2 flex flex-wrap items-center gap-4">
                                <p><b>Pratique n°<?= $i ?> :</b> <?php echo htmlspecialchars($pratique["pratique"]); ?></p>
                                <a class="underline text-secondary" onclick="openFile('<?= base64_encode($pratique['justificatif']) ?>')">Voir le justificatif</a>
                                <form method="POST" data-confirm="1">
                                    <input type="hidden" name="removePratique" value="1">
                                    <input type="hidden" name="justif_id" value="<?= $pratique["id"] ?>">
                                    <button type="submit" class="underline text-red-500">Supprimer</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Aucune pratique enregistrée.</p>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" data-confirm="1" class="mt-4">
                        <input type="hidden" name="addPratique" value="1">
                        <h3 class="mt-3">Ajouter une pratique</h3>
                        <div class="flex flex-col">
                            <label for="pratique_name">Nom de la pratique</label>
                            <input class="input-primary" type="text" id="pratique_name" name="pratique_name" required>
                        </div>
                        <div class="flex flex-col">
                            <label for="pratique_file">Justificatif (obligatoire)</label>
                            <input class="input-primary" type="file" id="pratique_file" name="pratique_file" accept=".jpeg,.jpg,.png,.pdf" required>
                        </div>
                        <button type="submit" class="btn-primary">Ajouter la pratique</button>
                    </form>
                </div>

                <div class="mt-6">
                    <h2><b>Justificatifs :</b></h2>

                    <div class="mt-3 p-3 border-2 border-secondary rounded-md">
                        <p>
                            <b>SIRET :</b>
                            <a class="ml-2 underline text-secondary" onclick="openFile('<?= base64_encode($praticien_user['siret_justificatif']) ?>')">Voir le fichier</a>
                        </p>
                        <form method="POST" enctype="multipart/form-data" data-confirm="1" class="mt-2">
                            <input type="hidden" name="updateSiretJustif" value="1">
                            <div class="flex flex-col">
                                <label for="siret_file">Nouveau justificatif</label>
                                <input class="input-primary" type="file" id="siret_file" name="siret_file" accept=".jpeg,.jpg,.png,.pdf" required>
                            </div>
                            <button type="submit" class="underline text-primary mt-2">Mettre à jour le justificatif SIRET</button>
                        </form>
                    </div>

                    <div class="mt-3 p-3 border-2 border-secondary rounded-md">
                        <p>
                            <b>Assurance :</b>
                            <a class="ml-2 underline text-secondary" onclick="openFile('<?= base64_encode($praticien_user['assurance_justificatif']) ?>')">Voir le fichier</a>
                        </p>
                        <form method="POST" enctype="multipart/form-data" data-confirm="1" class="mt-2">
                            <input type="hidden" name="updateAssuranceJustif" value="1">
                            <div class="flex flex-col">
                                <label for="assurance_file">Nouveau justificatif</label>
                                <input class="input-primary" type="file" id="assurance_file" name="assurance_file" accept=".jpeg,.jpg,.png,.pdf" required>
                            </div>
                            <button type="submit" class="underline text-primary mt-2">Mettre à jour le justificatif d'assurance</button>
                        </form>
                    </div>

                    <?php if ($pratiques && count($pratiques) > 0): ?>
                        <?php foreach ($pratiques as $pratique): ?>
                            <div class="mt-3 p-3 border-2 border-secondary rounded-md">
                                <p>
                                    <b>Pratique : <?php echo htmlspecialchars($pratique["pratique"]); ?></b>
                                    <a class="ml-2 underline text-secondary" onclick="openFile('<?= base64_encode($pratique['justificatif']) ?>')">Voir le fichier</a>
                                </p>
                                <form method="POST" enctype="multipart/form-data" data-confirm="1" class="mt-2">
                                    <input type="hidden" name="updatePratiqueJustif" value="1">
                                    <input type="hidden" name="justif_id" value="<?= $pratique["id"] ?>">
                                    <div class="flex flex-col">
                                        <label>Nouveau justificatif</label>
                                        <input class="input-primary" type="file" name="pratique_file" accept=".jpeg,.jpg,.png,.pdf" required>
                                    </div>
                                    <button type="submit" class="underline text-primary mt-2">Mettre à jour le justificatif de cette pratique</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
}
?>

<script>
const popupFile = document.getElementById('popupFile');
let popupFileFrame = null;
if (popupFile) {
    popupFileFrame = popupFile.querySelector('.popup-frame');
}

function openFile(base64) {
    if (!popupFile) return;
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
    if (!popupFile) return;
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

if (popupFile) {
    popupFile.addEventListener('click', closeFile);
}
if (popupFileFrame) {
    popupFileFrame.addEventListener('click', (event) => {
        event.stopPropagation();
    });
}

document.addEventListener("DOMContentLoaded", function() {
    const confirmPopup = document.getElementById("accountModifPopup");
    if (!confirmPopup) return;
    const confirmFrame = confirmPopup.querySelector(".popup-frame");
    const btnOui = document.getElementById("accountModifBtnOui");
    const btnNon = document.getElementById("accountModifBtnNon");
    let pendingForm = null;

    document.querySelectorAll('form[data-confirm="1"]').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (this.dataset.confirmed === "1") {
                this.dataset.confirmed = "0";
                pendingForm = null;
                confirmPopup.style.display = "none";
                return true;
            }
            e.preventDefault();
            e.stopPropagation();
            pendingForm = this;
            confirmPopup.style.display = "";
        });
    });

    if (btnOui) {
        btnOui.addEventListener('click', function() {
            if (!pendingForm) return;
            pendingForm.dataset.confirmed = "1";
            if (pendingForm.requestSubmit) {
                pendingForm.requestSubmit();
            } else {
                pendingForm.submit();
            }
            setTimeout(function() {
                if (pendingForm) pendingForm.dataset.confirmed = "0";
            }, 100);
        });
    }

    if (btnNon) {
        btnNon.addEventListener('click', function() {
            if (pendingForm) pendingForm.dataset.confirmed = "0";
            pendingForm = null;
            confirmPopup.style.display = "none";
        });
    }

    confirmPopup.addEventListener('click', function() {
        if (pendingForm) pendingForm.dataset.confirmed = "0";
        pendingForm = null;
        confirmPopup.style.display = "none";
    });

    confirmFrame.addEventListener('click', function(event) {
        event.stopPropagation();
    });
});
</script>