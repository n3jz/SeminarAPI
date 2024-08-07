<?php
header('Content-Type: application/json');
$servername = "localhost";
$username = "web_login";
$password = "-2YFqU.oK8[C_7Sn";
$dbname = "em";

// ustvarimo povezavo
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Predelamo JSON ki ga dobimoo 
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

// V eni zahtevi pošljemo podatke za samo eno zgradbo.
$p_building_name = $input['building_name'];
$p_building_location = $input['building_location'];
$results = [];

// Za vsak števec te zgrabe vpišemo podatke v bazo preko stored procedure
foreach ($input['meters'] as $meter) {
    $p_meter_name = $meter['meter_name'];
    $p_timestamp = $meter['timestamp'];
    $p_average_power = $meter['average_power'];
    $p_total_energy = $meter['total_energy'];

    $stmt = $conn->prepare("CALL AddBuildingWithMeter(?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssdd", $p_building_name, $p_building_location, $p_meter_name, $p_timestamp, $p_average_power, $p_total_energy);

    // pozenemo stored proceduro in vrnemo odgovor 
    if ($stmt->execute()) {
        array_push($results, array("meter" => $p_meter_name, "status" => "uspesno dodan"));
    } else {
        array_push($results, array("meter" => $p_meter_name, "status" => "failure", "Error" => $stmt->error));
    }
    $stmt->close();
}

$conn->close();

// Vrnemo statuse za vsak stevec (kasneje odstrani)
echo json_encode($results);
?>
