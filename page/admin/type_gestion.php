<?php
require_once './functions/connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/atelier.php';
error_reporting(E_ALL & ~E_WARNING);

$user = verif_token($_SESSION["token"]);
$access = $user["type"];
if (!isset($_SESSION["token"]) || empty($_SESSION["token"]) || !$access || $access != "admin") {
    include "./page/error/403.php";
    exit;
}

$TITLE = "Quotidio Planning - Admin";

$successMsg = '';
$errorMsg = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["add_type_submit"])) {
        $type_name = trim($_POST["add_type_input"]);
        if (!empty($type_name)) {
            add_atelier_type($type_name);
            $successMsg = "Type ajouté avec succès.";
        } else {
            $errorMsg = "Le nom du type ne peut pas être vide.";
        }
    }
    if (isset($_POST["delete_type_submit"], $_POST["type_to_delete"])) {
        $type_name = $_POST["type_to_delete"];
        delete_atelier_type($type_name);
        $successMsg = "Type supprimé avec succès.";
    }
}

$types = get_atelier_types();

?>
<div class="dashboard">
    <div class="dashboard-content">
        <?php include 'components/admin/sidebar.php'; ?>
        <div class="content">
            <div class="ml-5 mt-5" style="max-width:600px;">
                <h2>Gestion des types d'atelier</h2>
                <?php if ($successMsg): ?>
                    <div class="text-green-600 mb-2"><?php echo htmlspecialchars($successMsg); ?></div>
                <?php endif; ?>
                <?php if ($errorMsg): ?>
                    <div class="text-red-600 mb-2"><?php echo htmlspecialchars($errorMsg); ?></div>
                <?php endif; ?>

                <div>
                    <?php if (!empty($types)): ?>
                        <table class="min-w-full" style="min-width:300px;">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left">Type</th>
                                    <th class="px-4 py-2 text-left">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                       
                                <?php foreach ($types as $row): ?>
                                    <tr>
                                        <td class="px-4 py-2">
                                            <?php echo htmlspecialchars($row['name']); ?>
                                        </td>
                                        <td class="px-4 py-2">
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="type_to_delete" value="<?php echo htmlspecialchars($row['name']); ?>">
                                                <button class="danger" type="submit" name="delete_type_submit" onclick="return confirm('Supprimer ce type ?');">
                                                    Supprimer
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                   
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>Aucun type enregistré.</p>
                    <?php endif; ?>
                </div>

                <hr class="my-6">

                <form method="POST" class="flex items-center">
                    <input 
                        type="text" 
                        name="add_type_input" 
                        placeholder="Ajouter un nouveau type" 
                        class="border px-2 py-1 rounded mr-3"
                        required 
                    >
                    <button class="underline" type="submit" name="add_type_submit">Ajouter</button>
                </form>
            </div>
        </div>
    </div>
</div>