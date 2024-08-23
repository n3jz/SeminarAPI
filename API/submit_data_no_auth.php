<?php
header('Content-Type: application/json');

$valid_token = 'BAC'; // token za avtentikacijo

// Preveri token
if (!isset($_GET['token'])) {
    echo json_encode(["status" => "error", "message" => "No token provided"]);
    http_response_code(401); 
    exit();
}

$token = $_GET['token'];

// Validate the token
if ($token !== $valid_token) {
    echo json_encode(["status" => "error", "message" => "Invalid token"]);
    http_response_code(401); 
    exit();
}

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
        array_push($results, array("meter" => $p_meter_name, "status" => "successfully added"));
    } else {
        array_push($results, array("meter" => $p_meter_name, "status" => "failure", "Error" => $stmt->error));
    }
    $stmt->close();
}

$conn->close();

// Return statuses for each meter
echo json_encode($results);
?>
