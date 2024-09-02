<?php

// Vključite Composerjev autoloader za JWT knjižnico
require __DIR__ . '/../vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

// Nastavi vsebino odgovora kot JSON
header('Content-Type: application/json');

// Podatki za povezavo z bazo
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

// Pridobi JWT žeton iz Authorization glave
$headers = apache_request_headers();

if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(array("message" => "Manjkajoča Authorization glava."));
    exit();
}

$authHeader = $headers['Authorization'];
list($jwt) = sscanf($authHeader, 'Bearer %s');

if (!$jwt) {
    http_response_code(401);
    echo json_encode(array("message" => "Manjkajoč JWT žeton."));
    exit();
}

// Skrivni ključ za dekodiranje JWT
$key = "moja_skrivnost"; // Poskrbite, da je to močan in varen ključ

try {
    // Dekodiraj JWT žeton
    $decoded = JWT::decode($jwt, new Key($key, 'HS256'));

    // Če je dekodiranje uspešno, nadaljuj s poizvedbo v bazo
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

} catch (Exception $e) {
    // Če je napaka pri dekodiranju JWT
    http_response_code(401);
    echo json_encode(array("message" => "Neveljaven ali potekel JWT žeton."));
}

// Zapri povezavo z bazo
$conn->close();
?>
