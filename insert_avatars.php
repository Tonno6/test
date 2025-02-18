<?php
$host = "VM_IP"; // IP della VM
$port = 3306; // Porta del database
$dbname = "unitydb";
$username = "unity_user";
$password = "password";

header('Content-Type: application/json');

try {
    $mysqli = new mysqli($host, $username, $password, $dbname, $port);

    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $inputData = json_decode(file_get_contents('php://input'), true);

        if (isset($inputData['avatars']) && is_array($inputData['avatars'])) {
            // Prepara statement per verificare esistenza
            $checkStmt = $mysqli->prepare("SELECT IdAvatar FROM avatars WHERE IdAvatar = ?");
            if (!$checkStmt) {
                throw new Exception("Prepare check failed: " . $mysqli->error);
            }

            // Prepara statement per inserimento
            $insertStmt = $mysqli->prepare("INSERT INTO avatars (IdAvatar, GUID, ImagePath) VALUES (?, ?, ?)");
            if (!$insertStmt) {
                throw new Exception("Prepare insert failed: " . $mysqli->error);
            }

            $inserted = 0;
            $skipped = 0;

            foreach ($inputData['avatars'] as $avatar) {
                $id = $avatar['IdAvatar'];
                $guid = $avatar['GUID'];
                $imagePath = $avatar['ImagePath'];

                // Controlla se l'avatar esiste già
                $checkStmt->bind_param("i", $id);
                if (!$checkStmt->execute()) {
                    throw new Exception("Check execution failed: " . $checkStmt->error);
                }
                $result = $checkStmt->get_result();
                
                if ($result->num_rows === 0) {
                    // Inserisce se non esiste
                    $insertStmt->bind_param("iss", $id, $guid, $imagePath);
                    if (!$insertStmt->execute()) {
                        throw new Exception("Insert execution failed: " . $insertStmt->error);
                    }
                    $inserted++;
                } else {
                    $skipped++;
                }
            }

            $checkStmt->close();
            $insertStmt->close();

            echo json_encode([
                "status" => "success",
                "message" => "Avatars processed",
                "inserted" => $inserted,
                "skipped" => $skipped
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid input format"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
    }

    $mysqli->close();
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>
