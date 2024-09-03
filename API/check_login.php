<?php

header('Content-Type: application/json');

// Composerjev autoloader za JWT knjižnico
require __DIR__ . '/../vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$key = "moja_skrivnost"; // Secret key used for decoding the JWT

// Get the Authorization header from the request
$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : null;

if ($authHeader) {
    $token = str_replace('Bearer ', '', $authHeader); // Odstrani Bearer
    try {
        // Dekodiraj žeton
        $decoded = JWT::decode($token, new Key($key, 'HS256')); 
        echo json_encode(array("message" => "Dostop potrjen.", "status" => true));
    } catch (\Firebase\JWT\ExpiredException $e) {
        http_response_code(401);
        echo json_encode(array("message" => "Žeton je potekel.", "status" => false));
    } catch (\Firebase\JWT\SignatureInvalidException $e) {
        http_response_code(401);
        echo json_encode(array("message" => "Napačen podpis žetona.", "status" => false));
    } catch (\UnexpectedValueException $e) {
        http_response_code(401);
        echo json_encode(array("message" => "Napačen žeton.", "status" => false));
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Napaka pri obdelavi žetona: " . $e->getMessage(), "status" => false));
    }
} else {
    http_response_code(401);
    echo json_encode(array("message" => "Dostop zavrnjen. Žeton manjka.", "status" => false));
}
?>
