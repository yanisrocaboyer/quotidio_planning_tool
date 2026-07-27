<?php
require_once 'generation.php';
require_once 'db.php';

function login($username, $password, $type) {
    global $sql;
    try {
        $sqlQuery = "SELECT * FROM users WHERE identifiant = :username LIMIT 1";
        $sqlRequest = $sql->prepare($sqlQuery);
        $sqlRequest->execute([
            'username' => $username
        ]);
        $sqlResult = $sqlRequest->fetch(PDO::FETCH_ASSOC);

        if($sqlResult && password_verify($password, $sqlResult['password'])) {
            $_SESSION['token'] = $sqlResult['token'];
            header('Location: /page/'. $type .'/');
        } else {
            header("Location: ?err=INVALID_USER");
        }
    } catch (Exception $e) {
        echo $e;
    }
}

function register($post, $files) {
    global $sql;
    $response = [
        'success' => false,
        'errors' => [],
        'praticien_id' => null
    ];

    function sanitize($val) {
        return htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
    }

    $name = isset($post['lastname']) ? sanitize($post['lastname']) : null;
    $surname = isset($post['firstname']) ? sanitize($post['firstname']) : null;
    $email = isset($post['email']) ? sanitize($post['email']) : null;
    $tel = isset($post['tel']) ? sanitize($post['tel']) : null;
    $siret = isset($post['siret']) ? sanitize($post['siret']) : null;
    $assurance = isset($post['assurance']) ? sanitize($post['assurance']) : null;
    $assurance_exp = isset($post['assurance_exp']) ? sanitize($post['assurance_exp']) : null;


    if ($email) {
        $emailCheck = $sql->prepare("SELECT id FROM praticien WHERE email = :email LIMIT 1");
        $emailCheck->execute(['email' => $email]);
        if ($emailCheck->fetch(PDO::FETCH_ASSOC)) {
            $response['success'] = true;
            return $response;
        }
    }

    if (!isset($files['siret_file']) || !isset($files['assurance_file'])) {
        $msg = "Les justificatifs obligatoires manquent.";
        error_log("[register] $msg");
        $response['errors'][] = $msg;
        return $response;
    }

    function getFileBlob($file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            error_log("[register] Fichier {$file['name']} upload error: code {$file['error']}");
            return false;
        }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpeg', 'jpg', 'png', 'pdf'])) {
            error_log("[register] Fichier {$file['name']} extension invalide: .{$ext}");
            return false;
        }
        $blob = file_get_contents($file['tmp_name']);
        if ($blob === false) {
            error_log("[register] Impossible de lire le contenu du fichier {$file['name']}");
            return false;
        }
        return $blob;
    }

    $siret_justificatif_blob = getFileBlob($files['siret_file']);
    if ($siret_justificatif_blob === false) {
        $msg = "Erreur lors de la lecture du justificatif de siret.";
        error_log("[register] $msg");
        $response['errors'][] = $msg;
        return $response;
    }

    $assurance_justificatif_blob = getFileBlob($files['assurance_file']);
    if ($assurance_justificatif_blob === false) {
        $msg = "Erreur lors de la lecture du justificatif d'assurance.";
        error_log("[register] $msg");
        $response['errors'][] = $msg;
        return $response;
    }

    try {
        $created_at = date('Y-m-d H:i:s');
        $insert = $sql->prepare("INSERT INTO praticien (user_id, name, surname, email, tel, siret, siret_justificatif, assurance, assurance_justificatif, assurance_date, state, created_at) 
            VALUES (NULL, :name, :surname, :email, :tel, :siret, :siret_justificatif, :assurance, :assurance_justificatif, :assurance_date, 'pending', :created_at)");

        $insert->bindParam(':name', $name);
        $insert->bindParam(':surname', $surname);
        $insert->bindParam(':email', $email);
        $insert->bindParam(':tel', $tel);
        $insert->bindParam(':siret', $siret);
        $insert->bindParam(':siret_justificatif', $siret_justificatif_blob, PDO::PARAM_LOB);
        $insert->bindParam(':assurance', $assurance);
        $insert->bindParam(':assurance_justificatif', $assurance_justificatif_blob, PDO::PARAM_LOB);
        $insert->bindParam(':assurance_date', $assurance_exp);
        $insert->bindParam(':created_at', $created_at);
        $insert->execute();

        $praticien_id = $sql->lastInsertId();
        if (!$praticien_id) {
            $msg = "Erreur lors de la création du praticien.";
            error_log("[register] $msg");
            $response['errors'][] = $msg;
            return $response;
        }
        $response['praticien_id'] = $praticien_id;

        if (isset($post['diploma_name']) && is_array($post['diploma_name']) && isset($files['diploma_file']) && is_array($files['diploma_file']['name'])) {
            $diplomaNames = $post['diploma_name'];
            $diplomaFiles = $files['diploma_file'];
            foreach ($diplomaNames as $idx => $diplomaName) {
                $nameDiploma = sanitize($diplomaName);
                if (
                    isset($diplomaFiles['error'][$idx]) &&
                    $diplomaFiles['error'][$idx] === UPLOAD_ERR_OK
                ) {
                    $fakeFile = [
                        'name' => $diplomaFiles['name'][$idx],
                        'type' => $diplomaFiles['type'][$idx],
                        'tmp_name' => $diplomaFiles['tmp_name'][$idx],
                        'error' => $diplomaFiles['error'][$idx],
                        'size' => $diplomaFiles['size'][$idx]
                    ];
                    $justificatif_blob = getFileBlob($fakeFile);
                    if ($justificatif_blob === false) {
                        $msg = "Erreur enregistrement du justificatif pour la pratique: " . $nameDiploma;
                        error_log("[register] $msg");
                        $response['errors'][] = $msg;
                        continue;
                    }
                    $insertJustif = $sql->prepare("INSERT INTO praticien_justificatif (praticien_id, pratique, justificatif) VALUES (:praticien_id, :pratique, :justificatif)");
                    $insertJustif->bindParam(':praticien_id', $praticien_id);
                    $insertJustif->bindParam(':pratique', $nameDiploma);
                    $insertJustif->bindParam(':justificatif', $justificatif_blob, PDO::PARAM_LOB);
                    $insertJustif->execute();
                } else {
                    $msg = "Fichier justificatif manquant/mal uploadé pour : " . $nameDiploma;
                    error_log("[register] $msg");
                    $response['errors'][] = $msg;
                }
            }
        } else {
            $msg = "Aucune pratique/diplôme reçue.";
            error_log("[register] $msg");
            $response['errors'][] = $msg;
        }

        if (empty($response['errors'])) {
            $response['success'] = true;
        }
    } catch(Exception $e) {
        $msg = "[register] Exception: " . $e->getMessage();
        error_log($msg);
        $response['errors'][] = $e->getMessage();
    }

    return $response;
}

function verif_token($token) {
    global $sql;
    try {
        $sqlQuery = "SELECT * FROM users WHERE token = :token LIMIT 1";
        $sqlRequest = $sql->prepare($sqlQuery);
        $sqlRequest->execute([
            'token' => $token
        ]);
        $sqlResult = $sqlRequest->fetch(PDO::FETCH_ASSOC);

        if ($sqlResult) {
            return $sqlResult;
        } else {
            return false;
        }
    } catch (Exception $e) {
        return false;
    }
}

function get_clients($signed_convention=False) {
    global $sql;
    if ($signed_convention){
        $request = $sql->prepare("SELECT * FROM client WHERE signed_convention=1");
    }
    else {
        $request = $sql->prepare("SELECT * FROM client");
    }
    $request->execute();
    return $request->fetchAll(PDO::FETCH_ASSOC);
}

function get_client($id) {
    global $sql;
    $request = $sql->prepare("SELECT * FROM client WHERE id=:id");
    $request->execute([
        "id" => $id
    ]);
    $result = $request->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        return $result;
    } else {
        return false;
    }
}

function get_praticiens($state) {
    global $sql;
    if ($state == "validate"){
        $request = $sql->prepare("SELECT * FROM praticien WHERE state='validate'");
    }
    else if ($state == "pending") {
        $request = $sql->prepare("SELECT * FROM praticien WHERE state='pending'");
    } else if ($state == "refuse") {
        $request = $sql->prepare("SELECT * FROM praticien WHERE state='refuse'");
    } else {
        $request = $sql->prepare("SELECT * FROM praticien");
    } 
    $request->execute();
    return $request->fetchAll(PDO::FETCH_ASSOC);
}

function get_praticien($id) {
    global $sql;
    $request = $sql->prepare("SELECT * FROM praticien WHERE id=:id");
    $request->execute([
        "id" => $id
    ]);
    $result = $request->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        return $result;
    } else {
        return false;
    }
}

function set_praticien_state($id, $state) {
    global $sql;
    $request = $sql->prepare("UPDATE `praticien` SET state=:state WHERE id=:id");
    $request->execute([
        "state" => $state,
        "id" => $id
    ]);
}

function set_praticien_to_valide($id) {
    global $sql;
    set_praticien_state($id, "validate");
    $praticien = get_praticien($id);
    try {
        $password = generateToken(12);
        $request = $sql->prepare("INSERT INTO `users` (identifiant, password, token, type, user_id) VALUES (:identifiant, :password, :token, 'praticien', :user_id) ");
        $request->execute([
            "identifiant" => $praticien["email"],
            "password" => password_hash($password, PASSWORD_BCRYPT),
            "token" => generateToken(32),
            "user_id" => $praticien["id"]
        ]);
        $user_id = $sql->lastInsertId();
    } catch (PDOException $e) {
        $user_id = null;
    }
    
    $request1 = $sql->prepare("UPDATE `praticien` SET user_id=:user_id WHERE id=:id");
    $request1->execute([
        "user_id" => $user_id,
        "id" => $id
    ]);

    return $password;
}

function refuse_del_praticien($id) {
    global $sql;
    set_praticien_state($id, "refuse");
    $request = $sql->prepare("DELETE FROM `users` WHERE user_id=:id");
    $request->execute([
        "id" => $id
    ]);
}

function delete_praticien($id) {
    global $sql;
    $request = $sql->prepare("DELETE FROM `praticien` WHERE id=:id");
    $request->execute([
        "id" => $id
    ]);
    $request1 = $sql->prepare("DELETE FROM `users` WHERE user_id=:id");
    $request1->execute([
        "id" => $id
    ]);
} 

function get_praticien_justificatif($id) {
    global $sql;
    $request = $sql->prepare("SELECT * FROM praticien_justificatif WHERE praticien_id=:id");
    $request->execute([
        "id" => $id
    ]);
    $result = $request->fetchAll(PDO::FETCH_ASSOC);
    if ($result) {
        return $result;
    } else {
        return false;
    }
}
