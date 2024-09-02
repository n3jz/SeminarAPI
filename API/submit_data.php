<?php
require __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');

$secret_key = "moja_skrivnost"; // Secret key for encoding/decoding JWT
$algorithm = "HS256"; // Algorithm used for JWT

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

// Extract the token from the "Authorization" header
$authHeader = $headers['Authorization'];
$token = str_replace('Bearer ', '', $authHeader);

try {
    // Decode the token
    $decoded = JWT::decode($token, new Key($secret_key, $algorithm));

    // You can optionally validate the claims in $decoded, like exp (expiration time)
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Invalid token"]);
    http_response_code(401);
    exit();
}

// Database connection and processing code...
$servername = "localhost";
$username = "web_login";
$password = "-2YFqU.oK8[C_7Sn";
$dbname = "em";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process the incoming JSON
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

// In one request, send data for only one building.
$p_building_name = $input['building_name'];
$p_building_location = $input['building_location'];
$results = [];

// For each meter of this building, insert data into the database via a stored procedure
foreach ($input['meters'] as $meter) {
    $p_meter_name = $meter['meter_name'];
    $p_timestamp = $meter['timestamp'];
    $p_average_power = $meter['average_power'];
    $p_total_energy = $meter['total_energy'];

    $stmt = $conn->prepare("CALL AddBuildingWithMeter(?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssdd", $p_building_name, $p_building_location, $p_meter_name, $p_timestamp, $p_average_power, $p_total_energy);

    // Execute the stored procedure and return the response
    if ($stmt->execute()) {
        array_push($results, array("meter" => $p_meter_name, "status" => "200"));
    } else {
        array_push($results, array("meter" => $p_meter_name, "status" => "failure", "Error" => $stmt->error));
    }
    $stmt->close();
}

$conn->close();

// Return statuses for each meter
echo json_encode($results);
?>
