<?php
// Funzione per salvare l'immagine Base64 sul server
function saveBase64Image($base64String, $outputFile) {
    list(, $data) = explode(',', $base64String); // Estrae solo i dati Base64
    $data = base64_decode($data); // Decodifica la stringa Base64
    
    if ($data === false) {
        throw new Exception("Errore nella decodifica Base64.");
    }
    
    file_put_contents($outputFile, $data);
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);

    if (isset($data['ImageBase64'])) {
        $base64String = $data['ImageBase64'];

        // Percorso della cartella di upload
        $outputDir = 'upload/';
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        // Genera un nome file univoco
        $uniqueId = uniqid();
        $outputFile = $outputDir . $uniqueId . '.png';

        try {
            saveBase64Image($base64String, $outputFile);

            // Percorso assoluto del file salvato (opzionale)
            $absolutePath = realpath($outputFile);

            echo json_encode([
                'status' => 'success',
                'image_path' => $outputFile,  // Percorso relativo
                'absolute_path' => $absolutePath // Percorso assoluto (solo per debug)
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Errore nel salvare l\'immagine: ' . $e->getMessage()
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
