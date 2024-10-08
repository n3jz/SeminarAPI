<?php

require __DIR__ . '/../vendor/autoload.php';
use \Firebase\JWT\JWT;

header('Content-Type: application/json');

// Prijava v bazo
$servername = getenv('DB_SERVERNAME');
$dbusername = getenv('DB_USERNAME');
$dbpassword = getenv('DB_PASSWORD'); 
$dbname = getenv('DB_NAME');

// povezava z bazo
$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Skrivni ključ za JWT
$key = "moja_skrivnost"; 
$issued_at = time();
$expiration_time = $issued_at + 3600; // 1 hour

// Get the POST data
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->username) && !empty($data->password)) {
    // Pripravimo poizvedbo za uporabnika
    $stmt = $conn->prepare("SELECT username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $data->username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Bind the result to variables
        $stmt->bind_result($db_username, $db_password_hash);
        $stmt->fetch();

        // Verify the password
        if (password_verify($data->password, $db_password_hash)) {
            $payload = array(
                "iat" => $issued_at,
                "exp" => $expiration_time,
                "data" => array(
                    "username" => $db_username // Store the username in the 'data' field
                )
            );

            // Generate JWT
            $jwt = JWT::encode($payload, $key, 'HS256');

            
            echo json_encode(array(
                "message" => "Prijava uspela.",
                "token" => $jwt
            ));
            exit();
        } else {
            
            http_response_code(401);
            echo json_encode(array("message" => "Geslo ni pravilno."));
            exit();
        }
    } else {
        http_response_code(401);
        echo json_encode(array("message" => "Uporabnik ni najden."));
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    
    http_response_code(400);
    echo json_encode(array("message" => "Manjkajoči podatki."));
    exit();
}
?>
