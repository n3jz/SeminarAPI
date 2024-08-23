<?php

session_start();

// Preveri, če je uporabnik prijavljen
if (!isset($_SESSION['username'])) {
    // Če ni seje, preusmerimo na login stran
    header("Location: /index.html");
    exit();
}

// Nastavi vsebino odgovora kot JSON
header('Content-Type: application/json');

// Podatki za povezavo z bazo - prilagodi za boljšo varnost
$servername = "localhost";
$username = "web_login";
$password = "-2YFqU.oK8[C_7Sn";
$dbname = "em";

// Ustvari povezavo
$conn = new mysqli($servername, $username, $password, $dbname);

// Preveri, če je povezava uspela
if ($conn->connect_error) {
    die("Povezava ni uspela: " . $conn->connect_error);
}

// Poizvedba za pridobitev podatkov o stavbah in števcih
$result = $conn->query("SELECT b.building_id, b.building_name, m.meter_id, m.meter_name FROM buildings b JOIN meters m ON b.building_id = m.building_id");

// Pripravi podatke za JSON odgovor
$data = [];
while ($row = $result->fetch_assoc()) {
    $building_id = $row['building_id'];
    if (!isset($data[$building_id])) {
        $data[$building_id] = [
            'building_id' => $row['building_id'],
            'building_name' => $row['building_name'],
            'meters' => []
        ];
    }
    $data[$building_id]['meters'][] = [
        'meter_id' => $row['meter_id'],
        'meter_name' => $row['meter_name']
    ];
}

// Vrni podatke kot JSON
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

// Zapri povezavo z bazo
$conn->close();

?>
