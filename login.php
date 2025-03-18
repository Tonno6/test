<?php
// Database connection details
$host = "192.168.0.197";
$port = 3306;
$dbname = "unitydb";
$username = "user";
$password = "0";

// Set header for JSON response
header('Content-Type: application/json');

// Encryption key (for transmission)
$encryption_key = 'latuachiavesegreta123456789012345';

// Connect to database
$conn = new mysqli($host, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Errore di connessione al database: ' . $conn->connect_error
    ]));
}

// Get data from POST
$username = $_POST['username'] ?? '';
$encrypted_password = $_POST['encrypted_password'] ?? '';

// Prevent SQL injection
$username = $conn->real_escape_string($username);

// Decode password from transmission
$decrypted_password = decrypt($encrypted_password, $encryption_key);

// Query database for password hash
$sql = "SELECT id, password_hash FROM LoginRPM WHERE username = '$username'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $stored_hash = $user['password_hash'];

    // Verify password with stored hash
    if (password_verify($decrypted_password, $stored_hash)) {
        // Login successful - generate session token
        $token = bin2hex(random_bytes(32));

        // Store token in database (need to add auth_token column to LoginRPM table)
        $user_id = $user['id'];
        $sql = "UPDATE LoginRPM SET last_login = NOW() WHERE id = $user_id";
        $conn->query($sql);

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

$conn->close();

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