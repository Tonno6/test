<?php
// Funzione per salvare l'immagine Base64 sul server
function saveBase64Image($base64String, $outputFile) {
    // Rimuove l'intestazione Base64, se presente (es. "data:image/png;base64,")
    if (strpos($base64String, ',') !== false) {
        list(, $base64String) = explode(',', $base64String);
    }
    
    // Decodifica la stringa Base64
    $data = base64_decode($base64String);
    if ($data === false) {
        throw new Exception("Errore nella decodifica Base64.");
    }
    
    // Scrive i dati binari in un file
    if (file_put_contents($outputFile, $data) === false) {
        throw new Exception("Errore nel salvataggio dell'immagine.");
    }
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Legge il corpo della richiesta
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);

    if (isset($data['ImageBase64'])) {
        $base64String = trim($data['ImageBase64']); // Rimuove eventuali spazi bianchi

        // Percorso della cartella di upload
        $outputDir = 'upload/';
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        // Genera un nome file univoco
        $uniqueId = uniqid();
        $outputFile = $outputDir . $uniqueId . '.png';

        try {
            // Salva l'immagine
            saveBase64Image($base64String, $outputFile);

            // Ottieni il percorso assoluto
            $absolutePath = realpath($outputFile);

            // Rispondi con i dettagli del file salvato
            echo json_encode([
                'status' => 'success',
                'image_path' => $outputFile, // Percorso relativo
                'absolute_path' => $absolutePath // Percorso assoluto per debug
            ], JSON_UNESCAPED_SLASHES);
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Errore: ' . $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'ImageBase64 non trovato nel JSON'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Metodo non consentito. Utilizzare POST'
    ]);
}
?>

