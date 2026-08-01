<?php
require_once './functions/connection.php';
require_once './functions/atelier.php';
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
    $result = edit_client($id, $_POST);

    if ($result != "error") {
        header("Location: ?id={$id}&error=clientUpdateSuccess");
        exit;
    } else {
        header("Location: ?id={$id}&error=clientUpdateFailed&msg=". $result);
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
                        <div>
                            <label for="typeInput">Type:</label>
                            <input type="text" id="typeInput" name="typeInput" value="<?php echo htmlspecialchars($atelier['type']) ?>" required>
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
                            <label for="dateInput">Date et heure:</label>
                            <input type="date" id="dateInput" name="dateInput" value="<?php echo htmlspecialchars($date) ?>" required>
                        </div>
                        <div>
                            <label for="hourInput">Date et heure:</label>
                            <input type="time" id="hourInput" name="hourInput" value="<?php echo htmlspecialchars($hour) ?>" required>
                        </div>
                        <div>
                            <label for="maxPartInput">Participants maximum:</label>
                            <input type="num" id="maxPartInput" name="maxPartInput" value="<?php echo htmlspecialchars($atelier['max_participants']) ?>" required>
                        </div>
                        <div>
                            <label for="partInput">Participants:</label>
                            <input type="num" id="partInput" name="partInput" value="<?php $atelier['participants'] ?>" required>
                        </div>
                        <button class="mt-2 underline" type="submit">Modifier les informations</button>
                    </form>
                </div>
                <div class="mt-2">
                    <h3><b>Afiliations :</b></h3>
                    <form method="POST">
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
                                        echo "<option value=\"$client_id\" $selected>$client_name</option>";
                                    }
                                }
                            $praticiens = get_praticiens("validate");
                            $selected_praticien_id = isset($atelier['praticien_id']) ? $atelier['praticien_id'] : null;
                            ?>
                            </select>
                        </div>
                        <div>
                            <label for="praticienInput">Praticien:</label>
                            <select name="praticienInput" id="praticienInput" required>
                                <option value="" disabled selected>Sélectionner un praticien</option>
                                <?php
                                if ($praticiens && is_array($praticien)) {
                                    foreach ($clients as $client) {
                                        $praticien_id = htmlspecialchars($praticien['id']);
                                        $praticien_surname = htmlspecialchars($praticien['surname']);
                                        $praticien_name = htmlspecialchars($praticien['name']);
                                        $selected = ($praticien_id == $selected_praticien_id) ? 'selected' : '';
                                        echo "<option value=\"$praticien_id\" $selected>$praticien_name $praticien_surname</option>";
                                
                                    }
                                }
                                ?>
                            </select>
                    </form>
            </div>
        </div>
    </div>
</div>
<?php
                }
}
}
?>