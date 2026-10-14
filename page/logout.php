<?php

if(!isset($_GET["type"])) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]);
    }
    session_destroy();
    header("Location: ./");
} else {
    require_once "functions/connection.php";
    login_as("admin", $_SESSION["adminId"], $_SESSION["adminTok"]);
    exit;
}