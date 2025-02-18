<?php
$host = "5.157.103.206"; // IP della VM
$port = 3306; // Porta del database
$dbname = "unitydb";
$username = "user";
$password = "0";

header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="avatars_export.json"'); // Forza il download del file

try {
    // Connessione al database
    $mysqli = new mysqli($host, $username, $password, $dbname, $port);

    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }

    // Query per ottenere tutti i dati dalla tabella avatars
    $query = "SELECT IdAvatar, GUID, ImagePath FROM avatars";
    $result = $mysqli->query($query);

    if (!$result) {
        throw new Exception("Query failed: " . $mysqli->error);
    }

    // Estrai i dati e formattali come array associativo
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    // Converti l'array in JSON
    $jsonData = json_encode($data, JSON_PRETTY_PRINT);

    // Verifica se la conversione JSON ha avuto successo
    if ($jsonData === false) {
        throw new Exception("JSON encoding failed: " . json_last_error_msg());
    }

    // Output del JSON
    echo $jsonData;

    // Chiudi la connessione al database
    $mysqli->close();
} catch (Exception $e) {
    // In caso di errore, restituisci un messaggio di errore
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>