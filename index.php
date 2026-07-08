<?php

# ENV
require_once __DIR__ . '/vendor/autoload.php'; 
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/"); 
$dotenv->load();

session_start();
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

// Default values
$layout = './layout/default.php';
$maintenance = false;

$url = !isset($_GET['url']) ? 'index' : $_GET['url'];
if (isset($_GET['link'])) {
    $link = $_GET['link'];
}
$url = trim($url);
$BASE = $url;

if (isset($url) && preg_match('#^https?://#', $url)) { 
        $url = "$url";
} else {
    $url = "$url.php";
}
if ($BASE == "" || $BASE == "index" || $BASE == "index.html" || $BASE == "index.php" || $BASE == "") {
    $url = "index.php";
}

// Maintenance mode
if ($maintenance) {
    $url = "error/503.php";
}

// Page conf
$page_conf = [];

if (file_exists("page/$url") && (!isset($link) || !preg_match('#^https?://#', $link))) {
    ob_start();
    include "page/$url";
    $page_content = ob_get_clean();

    if (!empty($page_conf)) {
        $layout = $page_conf["mg_layout"] ?? $layout;
    }
}
    
// If Page exist
if (file_exists("page/$url")) {
    if ($isAjax) {
        require "page/$url";
    } else {
        if($layout != false){
            include $layout;
        } else {
            require "page/$url";
        }
    }
} else {
    if ($isAjax) {
        echo "Page non trouvée";
    } else {
        include $layout;
    }
}
