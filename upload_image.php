<?php
// Function to save the Base64 image to the server
function saveBase64Image($base64String, $outputFile) {
    // Remove the Base64 header if present (e.g., "data:image/png;base64,")
    if (strpos($base64String, ',') !== false) {
        list(, $base64String) = explode(',', $base64String);
    }
    
    // Decode the Base64 string
    $data = base64_decode($base64String);
    if ($data === false) {
        throw new Exception("Error decoding Base64.");
    }
    
    // Write the binary data to the file
    if (file_put_contents($outputFile, $data) === false) {
        throw new Exception("Error saving the image.");
    }
}

// Set the response header to JSON
header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read the body of the request
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);

    // Check if the 'ImageBase64' key exists in the request data
    if (isset($data['ImageBase64'])) {
        $base64String = trim($data['ImageBase64']); // Remove any leading/trailing whitespace

        // Set the upload directory
        $outputDir = 'upload/';
        if (!is_dir($outputDir)) {
            // Create the directory if it doesn't exist
            mkdir($outputDir, 0755, true);
        }

        // Generate a unique filename
        $uniqueId = uniqid();
        $outputFile = $outputDir . $uniqueId . '.png';

        try {
            // Save the image
            saveBase64Image($base64String, $outputFile);

            // Get the absolute file path
            $absolutePath = realpath($outputFile);

            // Respond with the file details (relative and absolute paths)
            echo json_encode([
                'status' => 'success',
                'image_path' => $outputFile, // Relative file path
                'absolute_path' => $absolutePath // Absolute path for debugging
            ], JSON_UNESCAPED_SLASHES);
        } catch (Exception $e) {
            // Handle errors during image saving
            echo json_encode([
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    } else {
        // If 'ImageBase64' is not found in the input data
        echo json_encode([
            'status' => 'error',
            'message' => 'ImageBase64 not found in JSON'
        ]);
    }
} else {
    // If the request method is not POST
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed. Use POST'
    ]);
}
?>