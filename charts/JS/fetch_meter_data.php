<?php
header('Access-Control-Allow-Origin: *'); // Allows all domains, adjust as needed for security
header('Content-Type: application/json');
$servername = "localhost";
$username = "web_login";
$password = "-2YFqU.oK8[C_7Sn";
$dbname = "em";
$meter_id = $_GET['meter_id'];  // Assume meter_id is passed as a query parameter

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$response = [
    'average_power' => [],
    'total_energy' => []
];

// Fetch average power data
$query = "SELECT timestamp, average_power FROM power_averages WHERE meter_id = $meter_id";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $response['average_power'][] = $row;
}

// Fetch total energy data
$query = "SELECT timestamp, total_energy FROM total_energy WHERE meter_id = $meter_id";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $response['total_energy'][] = $row;
}

echo json_encode($response);

$conn->close();
?>