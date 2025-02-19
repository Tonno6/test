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
        $avatarId = isset($_GET['id']) ? intval($_GET['id']) : null;
        
        $query = "SELECT IdAvatar, GUID, ImagePath FROM avatars";
        if ($avatarId) {
            $query .= " WHERE IdAvatar = ?";
        }

        $stmt = $mysqli->prepare($query);
        if ($avatarId) {
            $stmt->bind_param("i", $avatarId);
        }

        if (!$stmt->execute()) {
            throw new Exception("Query execution failed: " . $stmt->error);
        }

        $result = $stmt->get_result();
        $avatars = [];

        while ($row = $result->fetch_assoc()) {
            $imagePath = $row['ImagePath'];

            if (file_exists($imagePath)) {
                $imageData = file_get_contents($imagePath);

                if ($imageData !== false) {
                    $mimeType = mime_content_type($imagePath);
                    $avatars[] = [
                        'IdAvatar' => $row['IdAvatar'],
                        'GUID' => $row['GUID'],
                        'ImagePath' => $imagePath,
                        'ImageBase64' => 'data:' . $mimeType . ';base64,' . base64_encode($imageData)
                    ];
                } else {
                    $avatars[] = [
                        'IdAvatar' => $row['IdAvatar'],
                        'GUID' => $row['GUID'],
                        'ImagePath' => $imagePath,
                        'error' => 'Error reading image file'
                    ];
                }
            } else {
                $avatars[] = [
                    'IdAvatar' => $row['IdAvatar'],
                    'GUID' => $row['GUID'],
                    'ImagePath' => $imagePath,
                    'error' => 'Image file not found'
                ];
            }
        }

        $stmt->close();

        echo json_encode([
            "status" => !empty($avatars) ? "success" : "error",
            "avatars" => $avatars
        ]);
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