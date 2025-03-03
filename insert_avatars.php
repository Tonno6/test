<?php
// Database connection details
$host = "VM_IP"; // IP address of the VM
$port = 3306; // Database port (default MySQL port)
$dbname = "unitydb"; // Database name
$username = "unity_user"; // Database username
$password = "password"; // Database password

// Set the content type for the response to JSON
header('Content-Type: application/json');

try {
    // Attempt to connect to the database
    $mysqli = new mysqli($host, $username, $password, $dbname, $port);

    // Check if the connection failed
    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }

    // Check if the HTTP request method is POST
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Get and decode the incoming JSON data
        $inputData = json_decode(file_get_contents('php://input'), true);

        // Ensure the 'avatars' key exists and is an array
        if (isset($inputData['avatars']) && is_array($inputData['avatars'])) {
            // Prepare a statement to check if the avatar already exists in the database
            $checkStmt = $mysqli->prepare("SELECT IdAvatar FROM avatars WHERE IdAvatar = ?");
            if (!$checkStmt) {
                throw new Exception("Prepare check failed: " . $mysqli->error);
            }

            // Prepare a statement to insert a new avatar into the database
            $insertStmt = $mysqli->prepare("INSERT INTO avatars (IdAvatar, GUID, ImagePath) VALUES (?, ?, ?)");
            if (!$insertStmt) {
                throw new Exception("Prepare insert failed: " . $mysqli->error);
            }

            // Initialize counters for inserted and skipped avatars
            $inserted = 0;
            $skipped = 0;

            // Loop through each avatar in the provided data
            foreach ($inputData['avatars'] as $avatar) {
                // Extract avatar data
                $id = $avatar['IdAvatar'];
                $guid = $avatar['GUID'];
                $imagePath = $avatar['ImagePath'];

                // Check if the avatar already exists in the database
                $checkStmt->bind_param("i", $id);
                if (!$checkStmt->execute()) {
                    throw new Exception("Check execution failed: " . $checkStmt->error);
                }
                $result = $checkStmt->get_result();

                // If the avatar does not exist, insert it into the database
                if ($result->num_rows === 0) {
                    $insertStmt->bind_param("iss", $id, $guid, $imagePath);
                    if (!$insertStmt->execute()) {
                        throw new Exception("Insert execution failed: " . $insertStmt->error);
                    }
                    $inserted++; // Increment the inserted counter
                } else {
                    $skipped++; // Increment the skipped counter
                }
            }

            // Close the prepared statements
            $checkStmt->close();
            $insertStmt->close();

            // Return a JSON response with the number of inserted and skipped avatars
            echo json_encode([
                "status" => "success",
                "message" => "Avatars processed",
                "inserted" => $inserted,
                "skipped" => $skipped
            ]);
        } else {
            // Return an error if the 'avatars' field is missing or invalid
            echo json_encode(["status" => "error", "message" => "Invalid input format"]);
        }
    } else {
        // Return an error if the request method is not POST
        echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
    }

    // Close the database connection
    $mysqli->close();
} catch (Exception $e) {
    // Handle any exceptions and return an error message
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>