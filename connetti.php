<?php
$host = '5.157.103.206'; // Sostituisci con l'indirizzo IP del server MariaDB
$dbname = 'unitydb';
$username = 'user';
$password = '0';

// Crea una connessione
$conn = new mysqli($host, $username, $password, $dbname);

// Verifica la connessione
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

echo "Connessione al database remoto riuscita!";

// Chiudi la connessione (opzionale)
$conn->close();
?>