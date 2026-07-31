<?php
require_once 'generation.php';
require_once 'db.php';


function add_atelier($post) {
    global $sql;
    try {
        if (
            empty($post["typeInput"]) ||
            empty($post["dateInput"]) ||
            empty($post["hourInput"]) ||
            empty($post["clientInput"]) ||
            empty($post["maxPersonInput"])
        ) {
            return "error:missing_data";
        }

        $type = htmlspecialchars(trim($post["typeInput"]), ENT_QUOTES, "UTF-8");
        $date_part = htmlspecialchars(trim($post["dateInput"]), ENT_QUOTES, "UTF-8");
        $hour_part = htmlspecialchars(trim($post["hourInput"]), ENT_QUOTES, "UTF-8");
        $client_id = intval($post["clientInput"]);
        $max_participants = !empty($post["maxPersonInput"]) ? intval($post["maxPersonInput"]) : null;

        $date_hours_str = $date_part . " " . $hour_part . ":00";
        $date_hours = (new DateTime($date_hours_str))->format('Y-m-d H:i:s');
        $created_at = (new DateTime())->format('Y-m-d H:i:s');

        $sql_str = "INSERT INTO atelier (type, date_hours, state, max_participants, client_id, created_at) 
            VALUES (:type, :date_hours, :state, :max_participants, :client_id, :created_at)";
        $insert = $sql->prepare($sql_str);

        $insert->bindParam(':type', $type);
        $insert->bindParam(':date_hours', $date_hours);
        $state = "creation";
        $insert->bindParam(':state', $state);
        $insert->bindParam(':max_participants', $max_participants);
        $insert->bindParam(':client_id', $client_id);
        $insert->bindParam(':created_at', $created_at);

        $result = $insert->execute();

        if ($result) {
            return "success";
        } else {
            return "error:insert_failed";
        }
    } catch (Exception $e) {
        error_log("[add_atelier] Exception: " . $e->getMessage());
        return "error:" . $e->getMessage();
    }
}

function get_ateliers($type, $user="admin", $id=NULL) {
    if ($user == "admin") {
        if ($type == "pending") { 
            global $sql;
            $request = $sql->prepare("SELECT * FROM atelier WHERE state IN ('pending', 'creation')");
            $request->execute();
            return $request->fetchAll(PDO::FETCH_ASSOC);
        }
        elseif ($type == "futur") {
            global $sql;
            $request = $sql->prepare("SELECT * FROM atelier WHERE date_hours > NOW() AND state = 'validate'");
            $request->execute();
            return $request->fetchAll(PDO::FETCH_ASSOC);
        }
        elseif ($type == "pass") {
            global $sql;
            $request = $sql->prepare("SELECT * FROM atelier WHERE date_hours < NOW() AND state = 'validate'");
            $request->execute();
            return $request->fetchAll(PDO::FETCH_ASSOC);
        }
    } elseif ($user == "praticien") {
        if ($type == "futur") {
            global $sql;
            $request = $sql->prepare("SELECT * FROM atelier WHERE date_hours > NOW() AND state = 'validate' AND praticien_id=:id");
            $request->execute(["id" => $id]);
            return $request->fetchAll(PDO::FETCH_ASSOC);
        }
        elseif ($type == "pass") {
            global $sql;
            $request = $sql->prepare("SELECT * FROM atelier WHERE date_hours < NOW() AND state = 'validate' AND praticien_id=:id");
            $request->execute(["id" => $id]);
            return $request->fetchAll(PDO::FETCH_ASSOC);
        }
    } elseif ($user == "client") {
        if ($type == "futur") {
            global $sql;
            $request = $sql->prepare("SELECT * FROM atelier WHERE date_hours > NOW() AND state = 'validate' AND client_id=:id");
            $request->execute(["id" => $id]);
            return $request->fetchAll(PDO::FETCH_ASSOC);
        }
        elseif ($type == "pass") {
            global $sql;
            $request = $sql->prepare("SELECT * FROM atelier WHERE date_hours < NOW() AND state = 'validate' AND client_id=:id");
            $request->execute(["id" => $id]);
            return $request->fetchAll(PDO::FETCH_ASSOC);
        }
    } 
}

function get_atelier_types() {
    global $sql;
    $request = $sql->prepare("SELECT name FROM atelier_type");
    $request->execute();
    return $request->fetchAll(PDO::FETCH_ASSOC);
}