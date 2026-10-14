<?php
require_once 'generation.php';
require_once 'db.php';

function add_logs($user_id, $action) {
    global $sql;
    try {
        if (empty($user_id) || empty($action)) {
            return "error:missing_data";
        }

        $user_id = intval($user_id);
        $action = htmlspecialchars(trim($action), ENT_QUOTES, "UTF-8");

        $sql_str = "INSERT INTO logs (user_id, action) VALUES (:user_id, :action)";
        $insert = $sql->prepare($sql_str);
        $insert->bindParam(':user_id', $user_id);
        $insert->bindParam(':action', $action);

        $result = $insert->execute();

        if ($result) {
            return "success";
        } else {
            return "error:insert_failed";
        }
    } catch (Exception $e) {
        error_log("[add_logs] Exception: " . $e->getMessage());
        return "error:" . $e->getMessage();
    }
}