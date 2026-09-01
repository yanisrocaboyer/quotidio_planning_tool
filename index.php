<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

# ENV
require_once __DIR__ . '/vendor/autoload.php';

try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/");
    $dotenv->load();
} catch (Exception $e) {
    error_log("Erreur lors du chargement de .env : " . $e->getMessage());
    http_response_code(500);
    exit("Erreur de configuration du serveur.");
}

session_start();
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

$base_url = "";

// Mail Config

// Default values
$layout = './layout/default.php';
$maintenance = false;

$url = isset($_GET['url']) ? trim($_GET['url']) : 'index';
$link = isset($_GET['link']) ? $_GET['link'] : null;
$BASE = $url;

if (preg_match('#^https?://#', $url)) { 
    $requestedUrl = $url;
} else {
    $requestedUrl = preg_match('/\.php$/', $url) ? $url : "$url.php";
}
if ($BASE === "" || $BASE === "index" || $BASE === "index.html" || $BASE === "index.php") {
    $requestedUrl = "index.php";
}

// Maintenance mode
if ($maintenance) {
    $requestedUrl = "error/503.php";
}

// Page conf
$page_conf = [];

$pageFile = "page/$requestedUrl";
if (file_exists($pageFile) && (!isset($link) || !preg_match('#^https?://#', $link))) {
    ob_start();
    include $pageFile;
    $page_content = ob_get_clean();

    if (!empty($page_conf) && isset($page_conf["mg_layout"])) {
        $layout = $page_conf["mg_layout"];
    }
}

// Render logic
if (file_exists($pageFile)) {
    if ($isAjax) {
        require $pageFile;
    } else {
        if ($layout !== false && file_exists($layout)) {
            include $layout;
        } else {
            require $pageFile;
        }
    }
} else {
    if ($isAjax) {
        http_response_code(404);
        echo "Page non trouvée";
    } else {
        if (file_exists($layout)) {
            include $layout;
        } else {
            http_response_code(500);
            echo "Erreur interne du serveur.";
        }
    }
}
