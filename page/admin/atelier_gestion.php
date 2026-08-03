<?php
require_once './functions/connection.php';
require_once './functions/atelier.php';
require_once './functions/info.php';

error_reporting(E_ALL & ~E_WARNING);


$user = verif_token($_SESSION["token"]);
$access = $user["type"];
if (!isset($_SESSION["token"]) || empty($_SESSION["token"]) || !$access || $access != "admin") {
    include "./page/error/403.php";
}
else {

$TITLE = "Quotidio Planning - Admin";

if (!isset($_GET["id"])) {
    ?>
    <div class="dashboard">
    <div class="dashboard-content">
        <?php include 'components/admin/sidebar.php' ?> 
        <div class="content">
            <div class="ml-5 mt-5">
                <p>ID Manquant</p>
            </div>
        </div>
    </div>
    <?php
} else {
$id = $_GET["id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $result = edit_atelier($id, $_POST);

    if ($result != "error") {
        header("Location: ?id={$id}&error=atelierUpdateSuccess");
        exit;
    } else {
        header("Location: ?id={$id}&error=atelierUpdateFailed&msg=". $result);
    }
}

?>
<div class="dashboard">
    <div class="dashboard-content">
        <?php include 'components/admin/sidebar.php' ?> 
        <div class="content">
            <div class="ml-5 mt-5">
                <?php
                $atelier = get_atelier($id);
                if ($atelier["id"] == NULL) {
                    echo "<p>Atelier inconnu</p>";
                } else {
                $atelier_id = $atelier["id"];
                ?>
                <h2>Gestion d'atelier</h2>
                <?php
                if (isset($_GET["infoMsg"])) {
                    echo "<p class='text-blue-500'>{$_GET['infoMsg']}</p>";
                }
                ?>

                <!-- # Informations -->

                <form method="POST"">
                    <div class="mt-2">
                        <?php
                        if (isset($_GET["error"]) && $_GET["error"] == "atelierUpdateFailed") {
                            echo "<p class='text-red-500'>Error lors de l'édition de l'atelier: {$_GET['msg']} </p>";
                        } else if (isset($_GET["error"]) && $_GET["error"] == "atelierUpdateSuccess") {
                            echo "<p class='text-green-600'>Mise à jour de l'atelier réussis !</p>";
                        } else if (isset($_GET["infoMsg"])) {
                            echo "<p class='text-blue-500'>{$_GET['infoMsg']}</p>";
                        }
                        ?>
                        <h3 ><b>Informations :</b></h3>
                        <div>
                            <label for="typeInput">Type:</label>
                            <input type="text" id="typeInput" name="typeInput" value="<?php echo htmlspecialchars($atelier['type']) ?>" required  <?php if ($atelier['state'] === 'validate') echo 'disabled'; ?>>
                        </div>
                        <?php 
                        $date_hours = $atelier['date_hours'];
                        if (!empty($date_hours)) {
                            $dateObj = new DateTime($date_hours);
                            $date = $dateObj->format('Y-m-d');
                            $hour = $dateObj->format('H:i');
                        } else {
                            $date = '';
                            $hour = '';
                        }
                
                        ?>
                        <div>
                            <label for="dateInput">Date:</label>
                            <input type="date" id="dateInput" name="dateInput" value="<?php echo htmlspecialchars($date) ?>" required >
                        </div>
                        <div>
                            <label for="hourInput">Heure:</label>
                            <input type="time" id="hourInput" name="hourInput" value="<?php echo htmlspecialchars($hour) ?>" required>
                        </div>
                        <div>
                            <label for="maxPartInput">Participants maximum:</label>
                            <input type="num" id="maxPartInput" name="maxPartInput" value="<?php echo htmlspecialchars($atelier['max_participants']) ?>" required <?php if ($atelier['state'] === 'validate') echo 'disabled'; ?>>
                        </div>
                        <div>
                            <label for="partInput">Participants:</label>
                            <input type="num" id="partInput" name="partInput" value="<?php $atelier['participants'] ?>">
                        </div>
                    </div>
                    <div class="mt-2" id="affilations">
                        <h3><b>Afiliations :</b></h3>
                        <?php
                        $clients = get_clients();
                        $selected_client_id = isset($atelier['client_id']) ? $atelier['client_id'] : null;
                        ?>
                        <div>
                            <label for="clientInput">Client:</label>
                            <select name="clientInput" id="clientInput" required>
                                <option value="" disabled selected>Sélectionner un client</option>
                                <?php
                                if ($clients && is_array($clients)) {
                                    foreach ($clients as $client) {
                                        $client_id = htmlspecialchars($client['id']);
                                        $client_name = htmlspecialchars($client['name']);
                                        $selected = ($client_id == $selected_client_id) ? 'selected' : '';
                                        $disabled = ($atelier['state'] === 'validate') ? 'disabled' : '';
                                        echo "<option value=\"$client_id\" $selected $disabled>$client_name</option>";
                                    }
                                }
                            ?>
                            </select>
                        </div>
                        <div>
                            <label for="praticienInput">Praticien:</label>
                            <select name="praticienInput" id="praticienInput">
                                <option value="" disabled selected>Sélectionner un praticien</option>
                                <?php
                                $praticiens = get_praticiens("validate");
                                $selected_praticien_id = isset($atelier['praticien_id']) ? $atelier['praticien_id'] : null;
                                if ($praticiens && is_array($praticiens)) {
                                    foreach ($praticiens as $praticien) {
                                        $praticien_id = htmlspecialchars($praticien['id']);
                                        $praticien_surname = htmlspecialchars($praticien['surname']);
                                        $praticien_name = htmlspecialchars($praticien['name']);
                                        $selected = ($praticien_id == $selected_praticien_id) ? 'selected' : '';
                                        $selected = ($praticien_id == $selected_praticien_id) ? 'selected' : '';
                                        $disabled = ($atelier['state'] === 'validate') ? 'disabled' : '';
                                        echo "<option value=\"$praticien_id\" $selected $disabled>$praticien_name $praticien_surname</option>";                                
                                    }
                                }
                                ?>
                            </select>
                    <br><button class="mt-2 underline" type="submit">Modifier les informations</button>
                    </div>
                </form>

                <div class="mt-2">
                    <h3><b>Candidatures :</b></h3>
                </div>

                <!-- # Action -->

                <div class="mt-2">
                    <h3><b>Actions :</b></h3>
                    <?php
                    if ($atelier["state"] == 'creation') {
                       ?>
                       <a href="?action=setStateOpen&id=<?php echo $atelier['id'] ?>">Ouvrir les candidatures pour les praticiens</a><br>
                       <div class="flex">
                        <a href="?action=setStateValidate&id=<?php echo $atelier['id'] ?>">Valider l'atelier</a><br>
                        <?php
                        info("Valider l'atelier vous empêchera à la suite de modifier le type, le nombre de participants max, et les affiliations.");
                        if (isset($_GET['action']) && $_GET['action'] === 'setStateOpen' && isset($_GET['id']) && $_GET['id'] == $atelier["id"]) {
                            $result = set_state_atelier($atelier['id'], 'pending');
                            echo "<script>window.location.href = window.location.pathname + '?id=' + encodeURIComponent(" . $client["id"] . ");</script>";
                            header("Location: ?id={$atelier['id']}&infoMsg={$result}");
                        }
                        if (isset($_GET['action']) && $_GET['action'] === 'setStateValidate' && isset($_GET['id']) && $_GET['id'] == $atelier["id"]) {
                            $result = set_state_atelier($atelier['id'], 'validate');
                            echo "<script>window.location.href = window.location.pathname + '?id=' + encodeURIComponent(" . $client["id"] . ");</script>";
                            header("Location: ?id={$atelier['id']}&infoMsg={$result}");
                        }?>
                       </div>
                       <?php
                    } else if ($atelier["state"] == "pending") {
                        ?>
                        <div class="flex">
                        <a href="?action=setStateValidate&id=<?php echo $atelier['id'] ?>">Valider l'atelier</a><br>
                        <?php
                        info("Valider l'atelier vous empêchera à la suite de modifier le type, le nombre de participants max, et les affiliations.");
                        if (isset($_GET['action']) && $_GET['action'] === 'setStateValidate' && isset($_GET['id']) && $_GET['id'] == $atelier["id"]) {
                            $result = set_state_atelier($atelier['id'], 'validate');
                            echo "<script>window.location.href = window.location.pathname + '?id=' + encodeURIComponent(" . $client["id"] . ");</script>";
                            header("Location: ?id={$atelier['id']}&infoMsg={$result}");
                        }?>
                       </div>
                       <?php
                    }
                    ?>
                    <a href="?action=deleteAtelier&id=<?php echo $atelier['id'] ?>" class="danger">Supprimer l'atelier</a>
                    <?php
                    if (isset($_GET['action']) && $_GET['action'] === 'deleteAtelier' && isset($_GET['id']) && $_GET['id'] == $atelier["id"]) {
                            $result = del_atelier($atelier['id']);
                            echo "<script>window.location.href = window.location.pathname + '?id=' + encodeURIComponent(" . $client["id"] . ");</script>";
                            header("Location: .?id={$atelier['id']}&infoMsg={$result}");
                    }?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
                }
}
}
?>