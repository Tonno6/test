<?php
// Funzione per salvare l'immagine Base64 sul server
function saveBase64Image($base64String, $outputFile) {
    // Rimuove il prefisso "data:image/..." dalla stringa Base64
    list($type, $data) = explode(';', $base64String);
    list(, $data) = explode(',', $data);
    
    // Decodifica la stringa Base64
    $data = base64_decode($data);
    
    // Salva l'immagine sul server
    file_put_contents($outputFile, $data);
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Leggi il contenuto della richiesta POST
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);

    // Verifica se la chiave "ImageBase64" esiste nel JSON
    if (isset($data['ImageBase64'])) {
        // Estrai la stringa Base64
        $base64String = $data['ImageBase64'];
        
        // Definisci il percorso di output per l'immagine
        $outputDir = 'upload/';
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        // Genera un nome file univoco con timestamp
        $timestamp = time();
        $uniqueId = uniqid();
        $outputFile = $outputDir . $uniqueId . '.png';
        
        try {
            // Salva l'immagine sul server
            saveBase64Image($base64String, $outputFile);
            
            // Restituisci il percorso dell'immagine
            echo json_encode([
                'status' => 'success',
                'image_path' => $outputFile
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