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
$encryption_key = 'latuachiavesegreta12345678901234';

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

// Validate input
if (empty($username) || empty($encrypted_password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Username e password sono obbligatori'
    ]);
    exit;
}

// Prevent SQL injection
$username = $conn->real_escape_string($username);

// Check if username already exists
$sql = "SELECT id FROM LoginRPM WHERE username = '$username'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Username già in uso'
    ]);
    exit;
}

// Decode password from transmission
$decrypted_password = decrypt($encrypted_password, $encryption_key);

// Hash password for storage
$password_hash = password_hash($decrypted_password, PASSWORD_BCRYPT);

// Insert new user
$sql = "INSERT INTO LoginRPM (username, password_hash, registration_date) 
        VALUES ('$username', '$password_hash', NOW())";

if ($conn->query($sql) === TRUE) {
    // Registration successful
    echo json_encode([
        'success' => true,
        'message' => 'Registrazione completata con successo'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Errore durante la registrazione: ' . $conn->error
    ]);
}

$conn->close();

// Function to decode password from transmission
function decrypt($encrypted_text, $key)
{
    $encrypted_text = base64_decode($encrypted_text);
    
    // Extract IV from the beginning of the encrypted text
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