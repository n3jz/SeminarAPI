<?php

session_start();

if (!isset($_SESSION['username'])) {
    // Če ni seje preusmerimo na login stran
    header("Location: /index.html");
    exit();
}


// ZA TEST - prilagodi za bolšo varnost
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');
$servername = "localhost";
$username = "web_login";
$password = "-2YFqU.oK8[C_7Sn";
$dbname = "em";

// Ustvari povezavo
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Povezava ni uspela: " . $conn->connect_error);
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

/* OBLIKA PODATKOV KI JIH VRNEMO KOT JSON:
[
    [
        'building_id' => 1,
        'building_name' => 'Building A',
        'meters' => [
            ['meter_id' => 101, 'meter_name' => 'Meter 1'],
            ['meter_id' => 102, 'meter_name' => 'Meter 2']
        ]
    ],
    [
        'building_id' => 2,
        'building_name' => 'Building B',
        'meters' => [
            ['meter_id' => 103, 'meter_name' => 'Meter 3']
        ]
    ]
    // more buildings...
]
*/

$conn->close();
?>
