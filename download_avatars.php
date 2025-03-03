<?php
// Database connection details
$host = "VM_IP"; // Virtual Machine IP address
$port = 3306;
$dbname = "unitydb";
$username = "unity_user";
$password = "password";

// Set response content type to JSON
header('Content-Type: application/json');

try {
    // Establish connection to the database
    $mysqli = new mysqli($host, $username, $password, $dbname, $port);

    // Check if connection failed
    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }

    // Handle GET request
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $avatarId = isset($_GET['id']) ? intval($_GET['id']) : null;
        
        // SQL query to fetch avatars
        $query = "SELECT IdAvatar, GUID, ImagePath FROM avatars";
        if ($avatarId) {
            $query .= " WHERE IdAvatar = ?";
        }

        $stmt = $mysqli->prepare($query);
        if ($avatarId) {
            $stmt->bind_param("i", $avatarId);
        }

        // Execute the query
        if (!$stmt->execute()) {
            throw new Exception("Query execution failed: " . $stmt->error);
        }

        $result = $stmt->get_result();
        $avatars = [];

        // Process each retrieved avatar
        while ($row = $result->fetch_assoc()) {
            $imagePath = $row['ImagePath'];

            // Check if image file exists
            if (file_exists($imagePath)) {
                $imageData = file_get_contents($imagePath);

                if ($imageData !== false) {
                    $mimeType = mime_content_type($imagePath);
                    // Store avatar details including base64-encoded image
                    $avatars[] = [
                        'IdAvatar' => $row['IdAvatar'],
                        'GUID' => $row['GUID'],
                        'ImagePath' => $imagePath,
                        'ImageBase64' => 'data:' . $mimeType . ';base64,' . base64_encode($imageData)
                    ];
                } else {
                    // Error handling if image file cannot be read
                    $avatars[] = [
                        'IdAvatar' => $row['IdAvatar'],
                        'GUID' => $row['GUID'],
                        'ImagePath' => $imagePath,
                        'error' => 'Error reading image file'
                    ];
                }
            } else {
                // Error handling if image file is missing
                $avatars[] = [
                    'IdAvatar' => $row['IdAvatar'],
                    'GUID' => $row['GUID'],
                    'ImagePath' => $imagePath,
                    'error' => 'Image file not found'
                ];
            }
        }

        $stmt->close();

        // Return avatars data as JSON response
        echo json_encode([
            "status" => !empty($avatars) ? "success" : "error",
            "avatars" => $avatars
        ]);
    } else {
        // Handle invalid request methods
        echo json_encode([
            "status" => "error",
            "message" => "Only GET requests are allowed"
        ]);
    }

    $mysqli->close();
} catch (Exception $e) {
    // Handle errors and return JSON response
    echo json_encode([
        "status" => "error",
        "message" => "Error: " . $e->getMessage()
    ]);
}
?>