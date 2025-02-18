<?php
header('Content-Type: application/json');

$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$response = [
    'success' => false,
    'message' => 'Errore durante il caricamento',
    'image_path' => null
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    if (isset($data['image']) && isset($data['extension'])) {
        $imageData = base64_decode($data['image']);
        $fileName = uniqid() . '.' . $data['extension'];
        $targetFile = $uploadDir . $fileName;
        
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($data['extension'], $allowedExtensions) && file_put_contents($targetFile, $imageData)) {
            $response['success'] = true;
            $response['message'] = 'Caricamento riuscito';
            $response['image_path'] = $targetFile;
        } else {
            $response['message'] = 'Formato non supportato o errore di scrittura';
        }
    } else {
        $response['message'] = 'Dati non validi';
    }
}

echo json_encode($response);
?>