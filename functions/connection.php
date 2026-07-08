<?php
require_once 'generation.php';
require_once 'db.php';

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

    $uploadDir = __DIR__ . '/../uploads/praticien/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            $msg = "Échec de la création du dossier d'upload ($uploadDir).";
            error_log("[register] $msg");
            $response['errors'][] = $msg;
            return $response;
        }
    }

    function handleUpload($file, $prefix) {
        global $uploadDir;
        if ($file['error'] !== UPLOAD_ERR_OK) {
            error_log("[register] Fichier {$file['name']} upload error: code {$file['error']}");
            return false;
        }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpeg', 'jpg', 'png', 'pdf'])) {
            error_log("[register] Fichier {$file['name']} extension invalide: .{$ext}");
            return false;
        }
        $safeName = $prefix . '_' . uniqid() . '.' . $ext;
        $filePath = $uploadDir . $safeName;
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            return 'uploads/praticien/' . $safeName;
        }
        error_log("[register] move_uploaded_file failed for {$file['name']} to $filePath");
        return false;
    }

    $siret_justificatif = handleUpload($files['siret_file'], 'siret');
    if (!$siret_justificatif) {
        $msg = "Erreur lors de l'envoi du justificatif de siret.";
        error_log("[register] $msg");
        $response['errors'][] = $msg;
        return $response;
    }
    $assurance_justificatif = handleUpload($files['assurance_file'], 'assurance');
    if (!$assurance_justificatif) {
        $msg = "Erreur lors de l'envoi du justificatif d'assurance.";
        error_log("[register] $msg");
        $response['errors'][] = $msg;
        return $response;
    }
    
    try {
        $created_at = date('Y-m-d H:i:s');
        $insert = $sql->prepare("INSERT INTO praticien (user_id, name, surname, email, tel, siret, siret_justificatif, assurance, assurance_justificatif, state, created_at) 
            VALUES (NULL, :name, :surname, :email, :tel, :siret, :siret_justificatif, :assurance, :assurance_justificatif, 'pending', :created_at)");

        $insert->execute([
            'name' => $name,
            'surname' => $surname,
            'email' => $email,
            'tel' => $tel,
            'siret' => $siret,
            'siret_justificatif' => $siret_justificatif,
            'assurance' => $assurance,
            'assurance_justificatif' => $assurance_justificatif,
            'created_at' => $created_at,
        ]);

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
                    $justificatifPath = handleUpload($fakeFile, 'diplome' . $idx);
                    if (!$justificatifPath) {
                        $msg = "Erreur enregistrement du justificatif pour la pratique: " . $nameDiploma;
                        error_log("[register] $msg");
                        $response['errors'][] = $msg;
                        continue;
                    }
                    $insertJustif = $sql->prepare("INSERT INTO praticien_justificatif (praticien_id, pratique, justificatif) VALUES (:praticien_id, :pratique, :justificatif)");
                    $insertJustif->execute([
                        'praticien_id' => $praticien_id,
                        'pratique' => $nameDiploma,
                        'justificatif' => $justificatifPath
                    ]);
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