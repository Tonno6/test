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

// Percorso del file JSON
$jsonFile = 'input.json';

// Leggi il file JSON
$jsonData = file_get_contents($jsonFile);

// Decodifica il JSON
$data = json_decode($jsonData, true);

// Verifica se la chiave "ImageBase64" esiste nel JSON
if (isset($data['ImageBase64'])) {
    // Estrai la stringa Base64
    $base64String = $data['ImageBase64'];
    
    // Definisci il percorso di output per l'immagine
    $outputDir = 'images/';
    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0755, true);
    }
    $outputFile = $outputDir . uniqid() . '.png'; // Usa un nome univoco per l'immagine
    
    // Salva l'immagine sul server
    saveBase64Image($base64String, $outputFile);
    
    // Restituisci il percorso dell'immagine
    echo json_encode(['image_path' => $outputFile]);
} else {
    // Se "ImageBase64" non è presente nel JSON
    echo json_encode(['error' => 'ImageBase64 non trovato nel JSON']);
}
?>