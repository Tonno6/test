<?php
// Database connection details
$host = "192.168.0.197"; // IP address of the VM
$port = 3306; // Database port (default MySQL port)
$dbname = "unitydb"; // Database name
$username = "user"; // Database username
$password = "0"; // Database password

// Set the content type for the response to JSON
header('Content-Type: application/json');

// Encryption key (for transmission)
$encryption_key = 'latuachiavesegreta12345678901234';

try {
    // Attempt to connect to the database
    $mysqli = new mysqli($host, $username, $password, $dbname, $port);

    // Check if the connection failed
    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }

    // Get data from POST
    $username = $_POST['username'] ?? '';
    $encrypted_password = $_POST['encrypted_password'] ?? '';

    // Validate input
    if (empty($username) || empty($encrypted_password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Username e password sono obbligatori'
        ]);
        exit;
    }

    // Check if username already exists
    $checkStmt = $mysqli->prepare("SELECT id FROM LoginRPM WHERE username = ?");
    $checkStmt->bind_param("s", $username);
    
    if (!$checkStmt->execute()) {
        throw new Exception("Check execution failed: " . $checkStmt->error);
    }
    
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Username già in uso'
        ]);
        $checkStmt->close();
        $mysqli->close();
        exit;
    }
    
    $checkStmt->close();

    // Decode password from transmission
    $decrypted_password = decrypt($encrypted_password, $encryption_key);

    // Hash password for storage
    $password_hash = password_hash($decrypted_password, PASSWORD_BCRYPT);

    // Insert new user
    $insertStmt = $mysqli->prepare("INSERT INTO LoginRPM (username, password_hash, registration_date) VALUES (?, ?, NOW())");
    $insertStmt->bind_param("ss", $username, $password_hash);
    
    if (!$insertStmt->execute()) {
        throw new Exception("Insert execution failed: " . $insertStmt->error);
    }
    
    // Registration successful
    echo json_encode([
        'success' => true,
        'message' => 'Registrazione completata con successo'
    ]);
    
    // Close the statement
    $insertStmt->close();

    // Close the database connection
    $mysqli->close();
} catch (Exception $e) {
    // Handle any exceptions and return an error message
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}

// Function to decode password from transmission
function decrypt($encrypted_text, $key)
{
    $encrypted_text = base64_decode($encrypted_text);
    
    // IV handling from the original register.php
    $iv_size = 16; // AES block size in bytes
    $iv = substr($encrypted_text, 0, $iv_size);
    $encrypted_text = substr($encrypted_text, $iv_size);

    $decrypted = openssl_decrypt(
        $encrypted_text,
        'AES-256-CBC',
        $key,
        0,
        $iv
    );

    return $decrypted;
}
?>