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

    // Decode password from transmission
    $decrypted_password = decrypt($encrypted_password, $encryption_key);

    // Prepare a statement to query the database for password hash
    $stmt = $mysqli->prepare("SELECT id, password_hash FROM LoginRPM WHERE username = ?");
    $stmt->bind_param("s", $username);

    if (!$stmt->execute()) {
        throw new Exception("Query execution failed: " . $stmt->error);
    }

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $stored_hash = $user['password_hash'];

        // Verify password with stored hash
        if (password_verify($decrypted_password, $stored_hash)) {
            // Login successful - generate session token
            $token = bin2hex(random_bytes(32));

            // Store token in database (need to add auth_token column to LoginRPM table if needed)
            $user_id = $user['id'];
            $updateStmt = $mysqli->prepare("UPDATE LoginRPM SET last_login = NOW() WHERE id = ?");
            $updateStmt->bind_param("i", $user_id);
            $updateStmt->execute();
            $updateStmt->close();

            echo json_encode([
                'success' => true,
                'message' => 'Login riuscito',
                'token' => $token
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Password non corretta'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Utente non trovato'
        ]);
    }

    // Close the statement
    $stmt->close();

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
    $iv = str_repeat("\0", 16);  // 16 bytes of zeros IV

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