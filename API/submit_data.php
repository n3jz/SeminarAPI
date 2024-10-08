<?php
require __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');

$secret_key = "moja_skrivnost"; // Secret key for encoding/decoding JWT
$algorithm = "HS256"; 

// Get Authorization header
$headers = getallheaders();

if (!isset($headers['Authorization'])) {
    echo json_encode([
        "status" => "error",
        "message" => "No token provided",
        "received_headers" => $headers
    ]);
    http_response_code(401);
    exit();
}

// Token dobimo iz headerja
$authHeader = $headers['Authorization'];
$token = str_replace('Bearer ', '', $authHeader);

try {
    // Dekodiramo token
    $decoded = JWT::decode($token, new Key($secret_key, $algorithm));

    // You can optionally validate the claims in $decoded, like exp (expiration time)
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Invalid token"]);
    http_response_code(401);
    exit();
}

//Povezava z bazo
$servername = getenv('DB_SERVERNAME');
$username = getenv('DB_USERNAME');
$password = getenv('DB_PASSWORD');
$dbname = getenv('DB_NAME');

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process the incoming JSON
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

// Vsakič procesiramo samo eno stavbo
$p_building_name = $input['building_name'];
$p_building_location = $input['building_location'];
$results = [];

// Z uporabo stored procedure dodamo vsako stavbo
foreach ($input['meters'] as $meter) {
    $p_meter_name = $meter['meter_name'];
    $p_timestamp = $meter['timestamp'];
    $p_average_power = $meter['average_power'];
    $p_total_energy = $meter['total_energy'];

    $stmt = $conn->prepare("CALL AddBuildingWithMeter(?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssdd", $p_building_name, $p_building_location, $p_meter_name, $p_timestamp, $p_average_power, $p_total_energy);

    // Poskusimo izvesti stored procedure
    if ($stmt->execute()) {
        array_push($results, array("meter" => $p_meter_name, "status" => "200"));
    } else {
        array_push($results, array("meter" => $p_meter_name, "status" => "failure", "Error" => $stmt->error));
    }
    $stmt->close();
}

$conn->close();

// Vrnemo rezultate
echo json_encode($results);
?>
