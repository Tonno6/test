<?php
// Database connection details
$host = "5.157.103.206"; // IP address of the VM
$port = 3306; // Database port (default MySQL port)
$dbname = "unitydb"; // Database name
$username = "user"; // Database username
$password = "0"; // Database password

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
        // Get values from POST request
        $idmuseo = $_POST["id_museo"] ?? null;
        $idtotem = $_POST["id_totem"] ?? null;
        $urlglb = $_POST["url_glb"] ?? null;

        // Validate input data
        if ($idmuseo === null || $idtotem === null || $urlglb === null) {
            echo json_encode(["status" => "error", "message" => "Missing required fields"]);
            exit;
        }

        // Prepare a statement to check if the avatar already exists in the database
        $checkStmt = $mysqli->prepare("SELECT url_glb FROM avatars WHERE url_glb = ?");
        if (!$checkStmt) {
            throw new Exception("Prepare check failed: " . $mysqli->error);
        }
        $checkStmt->bind_param("s", $urlglb);
        if (!$checkStmt->execute()) {
            throw new Exception("Check execution failed: " . $checkStmt->error);
        }
        $result = $checkStmt->get_result();

        // If the avatar does not exist, insert it into the database
        if ($result->num_rows === 0) {
            $insertStmt = $mysqli->prepare("INSERT INTO avatars (id_museo, id_totem, url_glb) VALUES (?, ?, ?)");
            if (!$insertStmt) {
                throw new Exception("Prepare insert failed: " . $mysqli->error);
            }
            $insertStmt->bind_param("iis", $idmuseo, $idtotem, $urlglb);
            if (!$insertStmt->execute()) {
                throw new Exception("Insert execution failed: " . $insertStmt->error);
            }
            $insertStmt->close();
            $message = "Avatar inserted successfully";
        } else {
            $message = "Avatar already exists";
        }
        
        // Close the prepared statement
        $checkStmt->close();

        // Return a JSON response
        echo json_encode(["status" => "success", "message" => $message]);
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
