<?php
$host = "VM_IP"; // IP della VM
$port = 3306; // Porta del database
$dbname = "unitydb";
$username = "unity_user";
$password = "password";

header('Content-Type: application/json');

try {
    // Connessione al database usando MySQLi
    $mysqli = new mysqli($host, $username, $password, $dbname, $port);

    // Verifica se la connessione è riuscita
    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }

    // Controllo che ci sia una richiesta POST
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Leggi i dati JSON dalla richiesta
        $inputData = json_decode(file_get_contents('php://input'), true);

        if (isset($inputData['avatars']) && is_array($inputData['avatars'])) {
            // Preparazione della query SQL
            $stmt = $mysqli->prepare("INSERT INTO avatars (IdAvatar, GUID, ImagePath) VALUES (?, ?, ?)");

            // Ciclo per ogni avatar e inserimento dei dati
            foreach ($inputData['avatars'] as $avatar) {
                $stmt->bind_param("iss", $avatar['IdAvatar'], $avatar['GUID'], $avatar['ImagePath']);
                $stmt->execute();
            }

            // Risposta success
            echo json_encode(["status" => "success", "message" => "Avatars inserted successfully"]);
        } else {
            // Risposta errore
            echo json_encode(["status" => "error", "message" => "Invalid input format"]);
        }

        // Chiudi la preparazione della query
        $stmt->close();
    } else {
        // Risposta errore se non è una richiesta POST
        echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
    }

    // Chiudi la connessione al database
    $mysqli->close();
} catch (Exception $e) {
    // Risposta errore di connessione al DB
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>
