<?php
// Database connection details
$host = "192.168.0.197"; // IP address of the VM
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
        // Get and decode the incoming JSON data
        $inputData = json_decode(file_get_contents('php://input'), true);
        
        // Check if id_museo exists in the input
        if (!isset($inputData['id_museo'])) {
            throw new Exception("Missing id_museo field");
        }
        
        // Prepare a statement to insert data into the 'prova' table
        $insertStmt = $mysqli->prepare("INSERT INTO prova (id_museo) VALUES (?)");
        
        // Extract id_museo from the JSON
        $idMuseo = $inputData['id_museo'];
        
        // Bind parameter and execute the insert statement
        $insertStmt->bind_param("s", $idMuseo);
        
        if ($insertStmt->execute()) {
            // Return a successful JSON response
            echo json_encode([
                "status" => "success",
                "message" => "Data inserted",
                "id_museo" => $idMuseo
            ]);
        } else {
            // Return an error if insertion fails
            echo json_encode([
                "status" => "error", 
                "message" => "Insert failed: " . $insertStmt->error
            ]);
        }
        
        // Close the prepared statement
        $insertStmt->close();
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