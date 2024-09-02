<?php

// Nastavitve za CORS in vsebino
header('Content-Type: application/json');

// Vključite Composerjev autoloader za JWT knjižnico
require __DIR__ . '/../vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

// Povezava z bazo
$servername = "localhost";
$username = "web_login";
$password = "-2YFqU.oK8[C_7Sn";
$dbname = "em";

$conn = new mysqli($servername, $username, $password, $dbname);

// Preveri, če je prišlo do napake pri povezavi
if ($conn->connect_error) {
    error_log("Povezava ni uspela: " . $conn->connect_error);
    die("Povezava ni uspela");
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
} catch (Exception $e) {
    // Če je napaka pri dekodiranju JWT
    http_response_code(401);
    echo json_encode(array("message" => "Neveljaven ali potekel JWT žeton."));
    exit();
}

// Pridobi parametre iz URL-ja
$meter_id = isset($_GET['meter_id']) ? intval($_GET['meter_id']) : 0;
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

// Priprava odziva
$response = ['average_power' => [], 'total_energy' => []];

if ($meter_id > 0) {
    // Debug: zabeleži prejete datume
    error_log("ID stevca: " . $meter_id);
    error_log("Začetni datum: " . $start_date);
    error_log("Koncni datum: " . $end_date);

    // Priprava SQL poizvedbe za povprečno moč
    $query = "SELECT timestamp, average_power FROM power_averages WHERE meter_id = ?";
    if ($start_date && $end_date) {
        $query .= " AND timestamp BETWEEN ? AND ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iss", $meter_id, $start_date, $end_date);
    } else {
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $meter_id);
    }

    // Debug: zabeleži poizvedbo
    error_log("SQL poizvedba: " . $stmt->sqlstate);

    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $response['average_power'][] = $row;
    }
    $stmt->close();

    // Priprava SQL poizvedbe za skupno energijo
    $query = "SELECT timestamp, total_energy FROM total_energy WHERE meter_id = ?";
    if ($start_date && $end_date) {
        $query .= " AND timestamp BETWEEN ? AND ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iss", $meter_id, $start_date, $end_date);
    } else {
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $meter_id);
    }

    // Debug: zabeleži poizvedbo
    error_log("SQL poizvedba: " . $stmt->sqlstate);

    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $response['total_energy'][] = $row;
    }
    $stmt->close();
} else {
    error_log("Neveljaven meter_id: " . $meter_id);
    echo json_encode(['error' => 'Neveljaven meter_id']);
    $conn->close();
    exit;
}

// Pošlji JSON odgovor
echo json_encode($response);
$conn->close();

?>
