<?php
header('Access-Control-Allow-Origin: *'); // Allows all domains, adjust as needed for security
header('Content-Type: application/json');
$servername = "localhost";
$username = "web_login";
$password = "-2YFqU.oK8[C_7Sn";
$dbname = "em";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query("SELECT b.building_id, b.building_name, m.meter_id, m.meter_name FROM buildings b JOIN meters m ON b.building_id = m.building_id");
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[$row['building_id']]['building_id'] = $row['building_id'];
    $data[$row['building_id']]['building_name'] = $row['building_name'];
    $data[$row['building_id']]['meters'][] = [
        'meter_id' => $row['meter_id'],
        'meter_name' => $row['meter_name']
    ];
}
echo json_encode(array_values($data));

$conn->close();
?>
