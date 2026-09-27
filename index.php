<?php

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

# Configuration des erreurs (les erreurs ne sont affichés qu'en mode debug)
$app_debug = filter_var($_ENV["APP_DEBUG"] ?? false, FILTER_VALIDATE_BOOLEAN);
if ($app_debug) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
}

# Durcissement des cookies de session
#   httponly  : le cookie n'est pas accessible depuis le JavaScript
#   samesite  : protège contre la plupart des CSRF
#   secure    : activé uniquement en HTTPS
if (PHP_SESSION_ACTIVE !== session_status()) {
    session_set_cookie_params([
        "lifetime" => 0,
        "path" => "/",
        "httponly" => true,
        "samesite" => "Lax",
        "secure" => (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off")
    ]);
}
ini_set('session.use_strict_mode', '1');
session_start();

require_once __DIR__ . '/functions/security.php';

# En-têtes de sécurité
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: same-origin");
header("X-Frame-Options: SAMEORIGIN");
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
