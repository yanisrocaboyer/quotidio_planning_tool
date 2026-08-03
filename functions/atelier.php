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
            empty($post["timeInput"]) ||
            empty($post["maxPersonInput"])
        ) {
            return "error:missing_data";
        }

        $type = htmlspecialchars(trim($post["typeInput"]), ENT_QUOTES, "UTF-8");
        $date_part = htmlspecialchars(trim($post["dateInput"]), ENT_QUOTES, "UTF-8");
        $hour_part = htmlspecialchars(trim($post["hourInput"]), ENT_QUOTES, "UTF-8");
        $time_part = htmlspecialchars(trim($post["timeInput"]), ENT_QUOTES, "UTF-8");
        $client_id = intval($post["clientInput"]);
        $max_participants = !empty($post["maxPersonInput"]) ? intval($post["maxPersonInput"]) : null;

        $date_hours_str = $date_part . " " . $hour_part . ":00";
        $date_hours = (new DateTime($date_hours_str))->format('Y-m-d H:i:s');
        $created_at = (new DateTime())->format('Y-m-d H:i:s');

        $sql_str = "INSERT INTO atelier (type, date_hours, state, max_participants, client_id, created_at, duration) 
            VALUES (:type, :date_hours, :state, :max_participants, :client_id, :created_at, :time)";
        $insert = $sql->prepare($sql_str);

        $insert->bindParam(':type', $type);
        $insert->bindParam(':date_hours', $date_hours);
        $state = "creation";
        $insert->bindParam(':state', $state);
        $insert->bindParam(':max_participants', $max_participants);
        $insert->bindParam(':client_id', $client_id);
        $insert->bindParam(':created_at', $created_at);
        $insert->bindParam(':time', $time_part);
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

function edit_atelier($id, $post) {
    global $sql;
    try {
        if (
            empty($post["typeInput"]) ||
            empty($post["dateInput"]) ||
            empty($post["hourInput"]) ||
            empty($post["maxPartInput"])
        ) {
            return "error:missing_data";
        }

        $type = htmlspecialchars(trim($post["typeInput"]), ENT_QUOTES, "UTF-8");
        $date_part = htmlspecialchars(trim($post["dateInput"]), ENT_QUOTES, "UTF-8");
        $hour_part = htmlspecialchars(trim($post["hourInput"]), ENT_QUOTES, "UTF-8");

        $date_hours_str = $date_part . " " . $hour_part . ":00";
        $date_hours = (new DateTime($date_hours_str))->format('Y-m-d H:i:s');
        $max_participants = !empty($post["maxPartInput"]) ? intval($post["maxPartInput"]) : null;

        $client_id = (isset($post["clientInput"]) && $post["clientInput"] !== "") ? intval($post["clientInput"]) : null;
        $praticien_id = (isset($post["praticienInput"]) && $post["praticienInput"] !== "") ? intval($post["praticienInput"]) : null;
        $participants = (isset($post["partInput"]) && $post["partInput"] !== "") ? intval($post["partInput"]) : null;

        $sql_str = "UPDATE atelier SET 
                        type = :type, 
                        date_hours = :date_hours,
                        max_participants = :max_participants,
                        client_id = :client_id,
                        praticien_id = :praticien_id,
                        participants = :participants
                    WHERE id = :id";
        $update = $sql->prepare($sql_str);

        $update->bindParam(':type', $type);
        $update->bindParam(':date_hours', $date_hours);
        $update->bindParam(':max_participants', $max_participants);
        $update->bindParam(':client_id', $client_id);
        $update->bindParam(':praticien_id', $praticien_id);
        $update->bindParam(':participants', $participants);
        $update->bindParam(':id', $id);

        $result = $update->execute();

        if ($result) {
            return "success";
        } else {
            return "error:update_failed";
        }
    } catch (Exception $e) {
        error_log("[edit_atelier] Exception: " . $e->getMessage());
        return "error:" . $e->getMessage();
    }
}

function set_state_atelier($id, $state) {
    global $sql;
    $request = $sql->prepare("UPDATE atelier SET state = :state WHERE id = :id");
    $request->execute(["state" => $state, "id" => $id]);
}

function del_atelier($id) {
    global $sql;
    $request = $sql->prepare("DELETE FROM atelier WHERE id = :id");
    $request->execute(["id" => $id]);
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
        if ($type == "pending") { 
            global $sql;
            $request = $sql->prepare("SELECT * FROM atelier WHERE state = 'pending'");
            $request->execute();
            return $request->fetchAll(PDO::FETCH_ASSOC);
        }
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

function get_atelier($id) {
    global $sql;
    $request = $sql->prepare("SELECT * FROM atelier WHERE id = :id");
    $request->execute(["id" => $id]);
    return $request->fetch(PDO::FETCH_ASSOC);
}

# Atelier participations 

function get_participations_atelier($atelier_id) {
    global $sql;
    $request = $sql->prepare("SELECT * FROM participation_atelier WHERE atelier_id = :atelier_id");
    $request->execute(["atelier_id" => $atelier_id]);
    return $request->fetchAll(PDO::FETCH_ASSOC);
}

function get_participations_praticien($praticien_id, $limit="100") {
    global $sql;
    $request = $sql->prepare("SELECT * FROM participation_atelier WHERE praticien_id = :praticien_id LIMIT $limit");
    $request->execute(["praticien_id" => $praticien_id]);
    return $request->fetchAll(PDO::FETCH_ASSOC);
}

function add_participation_atelier($atelier_id, $praticien_id) {
    global $sql;
    $request = $sql->prepare("INSERT INTO participation_atelier (atelier_id, praticien_id, state) VALUES (:atelier_id, :praticien_id, 'pending')");
    $request->execute(["atelier_id" => $atelier_id, "praticien_id" => $praticien_id]);
}

function delete_participation_atelier($id) {
    global $sql;
    $request = $sql->prepare("DELETE FROM participation_atelier WHERE id = :id");
    $request->execute(["id" => $id]);
}

# Author

function get_client_note($client_id) {
    global $sql;
    $request = $sql->prepare("SELECT note FROM client WHERE id = :id");
    $request->execute(["id" => $client_id]);
    $result = $request->fetch(PDO::FETCH_ASSOC);
    return $result ? $result : "Aucun commentaire";
}