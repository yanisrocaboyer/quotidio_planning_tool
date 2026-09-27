<?php

function csrf_token() {
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf_token"];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, "UTF-8") . '">';
}

function csrf_check() {
    $token = isset($_POST["csrf_token"]) ? $_POST["csrf_token"] : (isset($_GET["csrf_token"]) ? $_GET["csrf_token"] : "");
    if (is_string($token) && $token !== "" && hash_equals(csrf_token(), $token)) {
        return true;
    }
    http_response_code(403);
    exit("Requête invalide (token de sécurité manquant ou invalide).");
}

function csrf_query() {
    return "csrf_token=" . urlencode(csrf_token());
}

function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

function set_flash($key, $value) {
    $_SESSION["flash"][$key] = $value;
}

function get_flash($key) {
    if (isset($_SESSION["flash"][$key])) {
        $value = $_SESSION["flash"][$key];
        unset($_SESSION["flash"][$key]);
        return $value;
    }
    return null;
}

function has_flash($key) {
    return isset($_SESSION["flash"][$key]);
}