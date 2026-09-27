<?php
require_once './functions/markdown.php';

$TITLE = "Quotidio Planning - Nouveautés";

$valid_types = help_update_types();
$type = isset($_GET["type"]) ? $_GET["type"] : null;
$ver = isset($_GET["ver"]) ? $_GET["ver"] : null;

if (!in_array($type, $valid_types, true)) {
    $type = "praticien";
}

if ($ver === null || !preg_match('/^[\w.-]+$/', $ver)) {
    header("Location: /help/updates.php?type=" . urlencode($type));
    exit;
}

$content = read_help_update($type, $ver);
?>
<div class="dashboard">
    <div class="dashboard-content">
        <div class="content-2">
            <div class="m-5">
                <p>
                    <a href="/help/updates.php?type=<?= isset($type) ? urlencode($type) : 'praticien' ?>" class="underline text-secondary">
                        &larr; Retour aux nouveautés
                    </a>
                </p>
                <?php if ($content): ?>
                    <h1 class="text-2xl flex items-center mt-2">
                        <?= htmlspecialchars($content['name']) ?>
                        <span class="ml-2 text-secondary">(v<?= htmlspecialchars($ver) ?>)</span>
                    </h1>
                    <div class="mt-4">
                        <?php echo md_to_html($content['body']); ?>
                    </div>
                <?php else: ?>
                    <h1 class="text-2xl flex items-center mt-2">Nouveauté introuvable</h1>
                    <p>Aucune mise à jour trouvée pour cette version.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>