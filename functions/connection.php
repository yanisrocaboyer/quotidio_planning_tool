<?php
require_once 'generation.php';
require_once 'db.php';
require_once 'mail.php';
require_once 'security.php';

function login($username, $password, $type) {
    global $sql, $base_url;
    try {
        $sqlQuery = "SELECT * FROM users WHERE identifiant = :username LIMIT 1";
        $sqlRequest = $sql->prepare($sqlQuery);
        $sqlRequest->execute([
            'username' => $username
        ]);
        $sqlResult = $sqlRequest->fetch(PDO::FETCH_ASSOC);

        if ($sqlResult && password_verify($password, $sqlResult['password'])) {
            session_regenerate_id(true);
            $_SESSION['token'] = $sqlResult['token'];
            header('Location: ' . $base_url . '/page/' . $type . '/');
        } else {
            header("Location: ?err=INVALID_USER");
        }
    } catch (Exception $e) {
        error_log("[login] Exception: " . $e->getMessage());
        header("Location: ?err=INVALID_USER");
    }
}

/**
 * Récupère un utilisateur via son id
 * @param int $user_id
 * @return array|false
 */
function get_user_by_id($user_id) {
    global $sql;
    try {
        $sqlQuery = "SELECT * FROM users WHERE id = :id LIMIT 1";
        $sqlRequest = $sql->prepare($sqlQuery);
        $sqlRequest->execute([
            'id' => $user_id
        ]);
        $sqlResult = $sqlRequest->fetch(PDO::FETCH_ASSOC);
        if ($sqlResult) {
            return $sqlResult;
        } else {
            return false;
        }
    } catch (Exception $e) {
        error_log("[get_user_by_id] Exception: " . $e->getMessage());
        return false;
    }
}

/**
 * @param int 
 * @param string 
 * @return string
 */
function update_user_password($user_id, $new_password) {
    global $sql;
    try {
        if (empty($user_id) || empty($new_password)) {
            return "error:missing_data";
        }

        $hashed = password_hash($new_password, PASSWORD_BCRYPT);
        $request = $sql->prepare("UPDATE users SET password = :password WHERE id = :id");
        $request->bindParam(":password", $hashed);
        $request->bindParam(":id", $user_id);
        $request->execute();

        if ($request->rowCount() === 0) {
            return "error:not_found_or_no_change";
        }
        return "success_pwd";
    } catch (Exception $e) {
        error_log("[update_user_password] Exception: " . $e->getMessage());
        return "error:" . $e->getMessage();
    }
}

/**
 * @param int 
 * @param string 
 * @return string 
 */
function update_user_identifiant($user_id, $new_identifiant) {
    global $sql;
    try {
        if (empty($user_id) || empty($new_identifiant)) {
            return "error:missing_data";
        }
        if (!filter_var($new_identifiant, FILTER_VALIDATE_EMAIL)) {
            return "error:invalid_email";
        }

        // Vérifie si cet identifiant n'existe pas déjà pour un autre utilisateur
        $check = $sql->prepare("SELECT id FROM users WHERE identifiant = :identifiant AND id != :id LIMIT 1");
        $check->execute(["identifiant" => $new_identifiant, "id" => $user_id]);
        if ($check->fetch(PDO::FETCH_ASSOC)) {
            return "error:identifiant_exists";
        }

        $request = $sql->prepare("UPDATE users SET identifiant = :identifiant WHERE id = :id");
        $request->bindParam(":identifiant", $new_identifiant);
        $request->bindParam(":id", $user_id);
        $request->execute();

        if ($request->rowCount() === 0) {
            return "error:not_found_or_no_change";
        }
        return "success";
    } catch (Exception $e) {
        error_log("[update_user_identifiant] Exception: " . $e->getMessage());
        return "error:" . $e->getMessage();
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

                    $html = '
                    <h1>Bonjour ' . htmlspecialchars($surname, ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . ',</h1>
                    <p>Nous vous informons que votre inscription en tant que praticien de Quotidio a bien été prise en compte.</p>
                    <p>Nous vous donnerons un retour concernant votre inscription dans les plus brefs délais.</p>
                    ';
                    send_mail($email, htmlspecialchars("Votre inscription a bien été prise en compte !"), $html, strip_tags($html));            
               
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

/* Clients */

function get_clients() {
    global $sql;
    $request = $sql->prepare("SELECT * FROM client");
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


function delete_client($id) {
    global $sql;
    try {
        $request = $sql->prepare("DELETE FROM `client` WHERE id=:id");
        $request->execute([
            "id" => $id
        ]);

        $request1 = $sql->prepare("DELETE FROM users WHERE user_id=:id AND type='client'");
        $request1->execute(["id" => $id]);

        return "accountDeleted";
    } catch (Exception $e) {
        error_log("[delete_client] Exception: " . $e->getMessage());
        return $e;
    }
}

function create_client_user($id) {
    global $sql;
    try {
        $client = get_client($id);
        if (!$client) {
            return false;
        }

        $email = $client["email"];
        $client_id = $client["id"];

        $password = generateToken(12);

        $request = $sql->prepare("INSERT INTO `users` (identifiant, password, token, type, user_id) VALUES (:identifiant, :password, :token, 'client', :user_id)");
        $request->execute([
            "identifiant" => $email,
            "password" => password_hash($password, PASSWORD_BCRYPT),
            "token" => generateToken(32),
            "user_id" => $client_id
        ]);

        $user_id = $sql->lastInsertId();

        $update = $sql->prepare("UPDATE `client` SET user_id=:user_id WHERE id=:id");
        $update->execute([
            "user_id" => $user_id,
            "id" => $client_id
        ]);

        $html = '
        <h1>Bonjour ' . htmlspecialchars($client['name'], ENT_QUOTES, 'UTF-8') . ',</h1>
        <p>Nous vous informons que votre compte partenaire a bien été créé. Vous pouvez retrouver vos identifiants ci-dessous :</p>
        <p>Identifiant : ' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '</p>
        <p>Mot de passe : ' . htmlspecialchars($password, ENT_QUOTES, 'UTF-8') . '</p>
        <p>Pour vous connecter à votre compte, rendez-vous sur <a href="https://quotidio.fr/connection/partenaire">https://quotidio.fr/connection/partenaire</a>.</p>
        ';
        send_mail($email, htmlspecialchars("Votre compte partenaire a été créé !"), $html, strip_tags($html));
   
        return $password;
    } catch (Exception $e) {
        error_log("[create_client_user] Exception: " . $e->getMessage());
        return false;
    }
}

function add_client($post) {
    global $sql;
    try {
        if (
            empty($post["nameInput"]) ||
            empty($post["adressInput"]) ||
            empty($post["emailInput"]) ||
            empty($post["telInput"])
        ) {
            return "error:missing_data";
        }

        $name = htmlspecialchars(trim($post["nameInput"]), ENT_QUOTES, "UTF-8");
        $adress = htmlspecialchars(trim($post["adressInput"]), ENT_QUOTES, "UTF-8");
        $email = htmlspecialchars(trim($post["emailInput"]), ENT_QUOTES, "UTF-8");
        $tel = htmlspecialchars(trim($post["telInput"]), ENT_QUOTES, "UTF-8");

        $sql_str = "INSERT INTO client (name, email, tel, adress, signed_convention) 
            VALUES (:name, :email, :tel, :adress, 1)";
        $insert = $sql->prepare($sql_str);

        $insert->bindParam(':name', $name);
        $insert->bindParam(':email', $email);
        $insert->bindParam(':tel', $tel);
        $insert->bindParam(':adress', $adress);

        $result = $insert->execute();
        $client_id = $sql->lastInsertId();

        $password = create_client_user($client_id);

        if ($result && $password) {
            return $password;
        } else {
            return "error:insert_failed";
        }
    } catch (Exception $e) {
        error_log("[add_client] Exception: " . $e->getMessage());
        return "error:" . $e->getMessage();
    }
}

function edit_client($id, $post) {
    global $sql;
    try {
        if (
            empty($post["nameInput"]) ||
            empty($post["adressInput"]) ||
            empty($post["emailInput"]) ||
            empty($post["telInput"]) ||
            empty($post["noteInput"])
        ) {
            return "error:missing_data";
        }

        $name = htmlspecialchars(trim($post["nameInput"]), ENT_QUOTES, "UTF-8");
        $adress = htmlspecialchars(trim($post["adressInput"]), ENT_QUOTES, "UTF-8");
        $email = htmlspecialchars(trim($post["emailInput"]), ENT_QUOTES, "UTF-8");
        $tel = htmlspecialchars(trim($post["telInput"]), ENT_QUOTES, "UTF-8");
        $note = htmlspecialchars(trim($post["noteInput"]), ENT_QUOTES, "UTF-8");

        $sql_str = "UPDATE client SET name = :name, adress = :adress, email = :email, tel = :tel, note = :note WHERE id = :id";
        $update = $sql->prepare($sql_str);

        $update->bindParam(':name', $name);
        $update->bindParam(':adress', $adress);
        $update->bindParam(':email', $email);
        $update->bindParam(':tel', $tel);
        $update->bindParam(':note', $note);
        $update->bindParam(':id', $id);

        $result = $update->execute();

        if ($result) {
            return "success";
        } else {
            return "error:update_failed";
        }
    } catch (Exception $e) {
        error_log("[edit_client] Exception: " . $e->getMessage());
        return "error:" . $e->getMessage();
    }
}

/* Praticiens */

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
    try {
        set_praticien_state($id, "validate");
        $praticien = get_praticien($id);

        $existing_user = false;
        $check = $sql->prepare("SELECT * FROM `users` WHERE identifiant = :identifiant AND type = 'praticien' LIMIT 1");
        $check->execute(["identifiant" => $praticien["email"]]);
        $existing_user = $check->fetch(PDO::FETCH_ASSOC);

        if (!$existing_user && !empty($praticien["user_id"])) {
            $check2 = $sql->prepare("SELECT * FROM `users` WHERE id = :id AND type = 'praticien' LIMIT 1");
            $check2->execute(["id" => $praticien["user_id"]]);
            $existing_user = $check2->fetch(PDO::FETCH_ASSOC);
        }

        if ($existing_user) {
            $link = $sql->prepare("UPDATE `praticien` SET user_id=:user_id WHERE id=:id");
            $link->execute(["user_id" => $existing_user["id"], "id" => $id]);
            return "account_exists";
        }

        $password = generateToken(12);
        $request = $sql->prepare("INSERT INTO `users` (identifiant, password, token, type, user_id) VALUES (:identifiant, :password, :token, 'praticien', :user_id) ");
        $request->execute([
            "identifiant" => $praticien["email"],
            "password" => password_hash($password, PASSWORD_BCRYPT),
            "token" => generateToken(32),
            "user_id" => $praticien["id"]
        ]);
        $user_id = $sql->lastInsertId();

        $request1 = $sql->prepare("UPDATE `praticien` SET user_id=:user_id WHERE id=:id");
        $request1->execute([
            "user_id" => $user_id,
            "id" => $id
        ]);

        $html = '
        <h1>Bonjour ' . htmlspecialchars($praticien["surname"], ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars($praticien["name"], ENT_QUOTES, 'UTF-8') . ',</h1>
        <p>Nous vous informons que votre compte praticien a bien été créé. Vous pouvez retrouver vos identifiants ci-dessous :</p>
        <p>Identifiant : ' . htmlspecialchars($praticien["email"], ENT_QUOTES, 'UTF-8') . '</p>
        <p>Mot de passe : ' . htmlspecialchars($password, ENT_QUOTES, 'UTF-8') . '</p>
        <p>Pour vous connecter à votre compte, rendez-vous sur <a href="https://quotidio.fr/connection/praticien">https://quotidio.fr/connection/praticien</a>.</p>
        ';
        send_mail($praticien["email"], htmlspecialchars("Votre compte praticien a été créé !"), $html, strip_tags($html));   

        return $password;
    } catch (Exception $e) {
        error_log("[set_praticien_to_valide] Exception: " . $e->getMessage());
        return false;
    }
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

function get_praticien_fichier_blob($file) {
    if (!isset($file) || !is_array($file) || $file["error"] !== UPLOAD_ERR_OK) {
        error_log("[get_praticien_fichier_blob] Fichier invalide ou erreur d'upload");
        return false;
    }
    $ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    if (!in_array($ext, ["jpeg", "jpg", "png", "pdf"])) {
        error_log("[get_praticien_fichier_blob] Extension invalide: ." . $ext);
        return false;
    }
    $blob = file_get_contents($file["tmp_name"]);
    if ($blob === false) {
        error_log("[get_praticien_fichier_blob] Impossible de lire le contenu du fichier");
        return false;
    }
    return $blob;
}

function update_praticien_info($id, $post) {
    global $sql;
    try {
        $praticien = get_praticien($id);
        if (!$praticien) {
            return "error:not_found";
        }

        $email = isset($post["email"]) ? htmlspecialchars(trim($post["email"]), ENT_QUOTES, "UTF-8") : "";
        $tel = isset($post["tel"]) ? htmlspecialchars(trim($post["tel"]), ENT_QUOTES, "UTF-8") : "";
        $siret = isset($post["siret"]) ? htmlspecialchars(trim($post["siret"]), ENT_QUOTES, "UTF-8") : "";
        $assurance = isset($post["assurance"]) ? htmlspecialchars(trim($post["assurance"]), ENT_QUOTES, "UTF-8") : "";
        $assurance_date = isset($post["assurance_date"]) ? htmlspecialchars(trim($post["assurance_date"]), ENT_QUOTES, "UTF-8") : "";

        if (empty($email) || empty($tel) || empty($siret)) {
            return "error:missing_data";
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "error:invalid_email";
        }

        $changed = ($email !== $praticien["email"])
            || ($tel !== $praticien["tel"])
            || ($siret !== $praticien["siret"])
            || ($assurance !== $praticien["assurance"])
            || ($assurance_date !== $praticien["assurance_date"]);

        if ($email !== $praticien["email"]) {
            $check = $sql->prepare("SELECT id FROM praticien WHERE email = :email AND id != :id LIMIT 1");
            $check->execute(["email" => $email, "id" => $id]);
            if ($check->fetch(PDO::FETCH_ASSOC)) {
                return "error:email_exists";
            }
            $updUser = $sql->prepare("UPDATE `users` SET identifiant=:identifiant WHERE user_id=:praticien_id AND type='praticien'");
            $updUser->execute(["identifiant" => $email, "praticien_id" => $id]);
        }

        $update = $sql->prepare("UPDATE praticien SET email=:email, tel=:tel, siret=:siret, assurance=:assurance, assurance_date=:assurance_date WHERE id=:id");
        $update->execute([
            "email" => $email,
            "tel" => $tel,
            "siret" => $siret,
            "assurance" => $assurance,
            "assurance_date" => $assurance_date,
            "id" => $id
        ]);

        if ($changed) {
            set_praticien_state($id, "pending");
        }

        return "success";
    } catch (Exception $e) {
        error_log("[update_praticien_info] Exception: " . $e->getMessage());
        return "error:" . $e->getMessage();
    }
}

function update_praticien_justificatif($id, $type, $file) {
    global $sql;
    try {
        $columns = [
            "siret" => "siret_justificatif",
            "assurance" => "assurance_justificatif"
        ];
        if (!isset($columns[$type])) {
            return "error:invalid_type";
        }

        $praticien = get_praticien($id);
        if (!$praticien) {
            return "error:not_found";
        }

        $blob = get_praticien_fichier_blob($file);
        if ($blob === false) {
            return "error:invalid_file";
        }

        $sql_str = "UPDATE praticien SET {$columns[$type]}=:blob WHERE id=:id";
        $request = $sql->prepare($sql_str);
        $request->bindParam(":blob", $blob, PDO::PARAM_LOB);
        $request->bindParam(":id", $id);
        $request->execute();

        set_praticien_state($id, "pending");
        return "success";
    } catch (Exception $e) {
        error_log("[update_praticien_justificatif] Exception: " . $e->getMessage());
        return "error:" . $e->getMessage();
    }
}

function add_pratique_justificatif($id, $pratique, $file) {
    global $sql;
    try {
        $pratique_name = htmlspecialchars(trim($pratique), ENT_QUOTES, "UTF-8");
        if (empty($pratique_name)) {
            return "error:missing_data";
        }

        $blob = get_praticien_fichier_blob($file);
        if ($blob === false) {
            return "error:invalid_file";
        }

        $insert = $sql->prepare("INSERT INTO praticien_justificatif (praticien_id, pratique, justificatif) VALUES (:praticien_id, :pratique, :justificatif)");
        $insert->bindParam(":praticien_id", $id);
        $insert->bindParam(":pratique", $pratique_name);
        $insert->bindParam(":justificatif", $blob, PDO::PARAM_LOB);
        $insert->execute();

        set_praticien_state($id, "pending");
        return "success";
    } catch (Exception $e) {
        error_log("[add_pratique_justificatif] Exception: " . $e->getMessage());
        return "error:" . $e->getMessage();
    }
}

function update_pratique_justificatif($id, $justif_id, $file) {
    global $sql;
    try {
        $blob = get_praticien_fichier_blob($file);
        if ($blob === false) {
            return "error:invalid_file";
        }

        $request = $sql->prepare("UPDATE praticien_justificatif SET justificatif=:blob WHERE id=:id AND praticien_id=:praticien_id");
        $request->bindParam(":blob", $blob, PDO::PARAM_LOB);
        $request->bindParam(":id", $justif_id);
        $request->bindParam(":praticien_id", $id);
        $request->execute();

        if ($request->rowCount() == 0) {
            return "error:not_found";
        }

        set_praticien_state($id, "pending");
        return "success";
    } catch (Exception $e) {
        error_log("[update_pratique_justificatif] Exception: " . $e->getMessage());
        return "error:" . $e->getMessage();
    }
}

function remove_pratique_justificatif($id, $justif_id) {
    global $sql;
    try {
        $request = $sql->prepare("DELETE FROM praticien_justificatif WHERE id=:id AND praticien_id=:praticien_id");
        $request->execute([
            "id" => $justif_id,
            "praticien_id" => $id
        ]);

        if ($request->rowCount() == 0) {
            return "error:not_found";
        }

        set_praticien_state($id, "pending");
        return "success";
    } catch (Exception $e) {
        error_log("[remove_pratique_justificatif] Exception: " . $e->getMessage());
        return "error:" . $e->getMessage();
    }
}
