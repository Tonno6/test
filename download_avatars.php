<?php
$host = "VM_IP"; // IP della VM
$port = 3306;
$dbname = "unitydb";
$username = "unity_user";
$password = "password";

header('Content-Type: application/json');

try {
    $mysqli = new mysqli($host, $username, $password, $dbname, $port);

    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }

    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        // Controlla se è stato fornito un ID specifico
        $avatarId = isset($_GET['id']) ? intval($_GET['id']) : null;
        
        if ($avatarId !== null) {
            // Query per un avatar specifico
            $stmt = $mysqli->prepare("SELECT IdAvatar, GUID, ImagePath FROM avatars WHERE IdAvatar = ?");
            $stmt->bind_param("i", $avatarId);
        } else {
            // Query per tutti gli avatar
            $stmt = $mysqli->prepare("SELECT IdAvatar, GUID, ImagePath FROM avatars");
        }

        if (!$stmt->execute()) {
            throw new Exception("Query execution failed: " . $stmt->error);
        }

        $result = $stmt->get_result();
        $avatars = [];

        while ($row = $result->fetch_assoc()) {
            $imagePath = $row['ImagePath'];
            
            // Verifica se il file esiste
            if (file_exists($imagePath)) {
                // Leggi l'immagine e convertila in base64
                $imageData = base64_encode(file_get_contents($imagePath));
                
                $avatars[] = [
                    'IdAvatar' => $row['IdAvatar'],
                    'GUID' => $row['GUID'],
                    'ImagePath' => $row['ImagePath'],
                    'ImageBase64' => 'data:image/png;base64,' . $imageData
                ];
            } else {
                // Se l'immagine non esiste, includi solo i dati dell'avatar senza l'immagine
                $avatars[] = [
                    'IdAvatar' => $row['IdAvatar'],
                    'GUID' => $row['GUID'],
                    'ImagePath' => $row['ImagePath'],
                    'error' => 'Image file not found'
                ];
            }
        }

        $stmt->close();

        if (empty($avatars)) {
            echo json_encode([
                "status" => "error",
                "message" => "No avatars found"
            ]);
        } else {
            echo json_encode([
                "status" => "success",
                "avatars" => $avatars
            ]);
        }
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Only GET requests are allowed"
        ]);
    }

    $mysqli->close();
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Error: " . $e->getMessage()
    ]);
}
?>