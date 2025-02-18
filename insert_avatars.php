<?php
$host = "VM_IP"; // IP della VM
$dbname = "unitydb";
$username = "unity_user";
$password = "password";

header('Content-Type: application/json');

try {
    // Connessione al database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Controllo che ci sia una richiesta POST
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Leggi i dati JSON dalla richiesta
        $inputData = json_decode(file_get_contents('php://input'), true);

        if (isset($inputData['avatars']) && is_array($inputData['avatars'])) {
            // Preparazione della query SQL
            $stmt = $pdo->prepare("INSERT INTO avatars (IdAvatar, GUID, ImagePath) VALUES (:IdAvatar, :GUID, :ImagePath)");

            // Ciclo per ogni avatar e inserimento dei dati
            foreach ($inputData['avatars'] as $avatar) {
                $stmt->bindParam(':IdAvatar', $avatar['IdAvatar']);
                $stmt->bindParam(':GUID', $avatar['GUID']);
                $stmt->bindParam(':ImagePath', $avatar['ImagePath']);
                $stmt->execute();
            }

            // Risposta success
            echo json_encode(["status" => "success", "message" => "Avatars inserted successfully"]);
        } else {
            // Risposta errore
            echo json_encode(["status" => "error", "message" => "Invalid input format"]);
        }
    } else {
        // Risposta errore se non è una richiesta POST
        echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
    }
} catch (PDOException $e) {
    // Risposta errore di connessione al DB
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>