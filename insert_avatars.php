<?php
include_once "/var/www/private/utils/config.php";  // Includi il file di configurazione
include_once SCRIPTS_PATH . "utils/config.php";     // Includi la configurazione aggiuntiva

function aggiungiAvatar($avatars)
{
    $host = "5.157.103.206"; // IP della VM
    $port = 3306;
    $dbname = "unitydb";
    $username = "user";
    $password = "0";

    // Connessione al database
    $conn = new mysqli($host, $username, $password, $dbname, $port);

    // Controlla se la connessione ha avuto successo
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Ciclo per ogni avatar
    foreach ($avatars as $avatar) {
        // Preparazione della query SQL
        $stmt = $conn->prepare("INSERT INTO avatars (IdAvatar, GUID, ImagePath) VALUES (?, ?, ?)");
        $stmt->bind_param('iss', $avatar['IdAvatar'], $avatar['GUID'], $avatar['ImagePath']);

        // Esegui la query
        if (!$stmt->execute()) {
            echo json_encode(["status" => "error", "message" => "Failed to insert avatar: " . $stmt->error]);
            $conn->close();
            exit();
        }
    }

    // Risposta success
    echo json_encode(["status" => "success", "message" => "Avatars inserted successfully"]);

    // Chiudi la connessione
    $conn->close();
}
?>