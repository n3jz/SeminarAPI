<?php
// Include the Composer autoloader
require __DIR__ . '/../vendor/autoload.php';
use \Firebase\JWT\JWT;

// Database credentials
$servername = "localhost";
$dbusername = "web_login"; 
$dbpassword = "-2YFqU.oK8[C_7Sn"; 
$dbname = "em";

// Create connection
$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Your secret key for JWT
$key = "moja_skrivnost"; // Use a strong secret key
$issued_at = time();
$expiration_time = $issued_at + 3600; // JWT valid for 1 hour

// Get the POST data
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->username) && !empty($data->password)) {
    // Prepare SQL statement to find the user
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

            // Return the token in the response body
            echo json_encode(array(
                "message" => "Successful login.",
                "token" => $jwt
            ));
            exit();
        } else {
            // Invalid password
            http_response_code(401);
            echo json_encode(array("message" => "Login failed. Invalid username or password."));
            exit();
        }
    } else {
        // User not found
        http_response_code(401);
        echo json_encode(array("message" => "Login failed. Invalid username or password."));
        exit();
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
} else {
    // Invalid request, missing username or password
    http_response_code(400);
    echo json_encode(array("message" => "Login failed. Data is incomplete."));
    exit();
}
?>
