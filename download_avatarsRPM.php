<?php
// Database connection details
$host = "192.168.0.197"; // IP address of the VM
$port = 3306; // Database port (default MySQL port)
$dbname = "unitydb"; // Database name
$username = "user"; // Database username
$password = "0"; // Database password

// Set the content type for the response to JSON
header('Content-Type: application/json');

try {
    // Attempt to connect to the database
    $mysqli = new mysqli($host, $username, $password, $dbname, $port);

    // Check if the connection failed
    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }

    // Check if the HTTP request method is GET
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        // Initialize an array to store the avatars
        $avatarsRPM = [];

        // Optional filtering parameters
        $idMuseo = isset($_GET['id_museo']) ? $_GET['id_museo'] : null;
        $idTotem = isset($_GET['id_totem']) ? $_GET['id_totem'] : null;

        // Prepare the SQL query based on provided filters
        $sql = "SELECT id_avatar, id_museo, id_totem, url_glb, token FROM avatarRPM";
        $conditions = [];
        $params = [];
        $types = "";

        if ($idMuseo !== null) {
            $conditions[] = "id_museo = ?";
            $params[] = $idMuseo;
            $types .= "s";
        }

        if ($idTotem !== null) {
            $conditions[] = "id_totem = ?";
            $params[] = $idTotem;
            $types .= "i";
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        // Prepare and execute the query
        $stmt = $mysqli->prepare($sql);

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            throw new Exception("Query execution failed: " . $stmt->error);
        }

        $result = $stmt->get_result();

        // Fetch all avatars
        while ($row = $result->fetch_assoc()) {
            $avatarsRPM[] = [
                'id_avatar' => $row['id_avatar'],
                'id_museo' => $row['id_museo'],
                'id_totem' => $row['id_totem'],
                'url_glb' => $row['url_glb'],
                'token' => $row['token']
            ];
        }

        // Close the statement
        $stmt->close();

        // Return the avatars as JSON
        echo json_encode([
            "status" => "success",
            "avatarsRPM" => $avatarsRPM,
            "count" => count($avatarsRPM)
        ]);
    } else {
        // Return an error if the request method is not GET
        echo json_encode(["status" => "error", "message" => "Only GET requests are allowed"]);
    }

    // Close the database connection
    $mysqli->close();
} catch (Exception $e) {
    // Handle any exceptions and return an error message
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>