<?php
// Enable detailed error reporting for debugging purposes
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Vključite Composerjev autoloader za JWT knjižnico
require __DIR__ . '/../vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$key = "moja_skrivnost"; // Secret key used for decoding the JWT

// Get the Authorization header from the request
$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : null;

if ($authHeader) {
    $token = str_replace('Bearer ', '', $authHeader); // Remove 'Bearer ' prefix
    try {
        // Decode the token
        $decoded = JWT::decode($token, new Key($key, 'HS256')); // Note the use of new Key() here
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
        // General exception handling for other unforeseen errors
        http_response_code(500);
        echo json_encode(array("message" => "Napaka pri obdelavi žetona: " . $e->getMessage(), "status" => false));
    }
} else {
    http_response_code(401);
    echo json_encode(array("message" => "Dostop zavrnjen. Žeton manjka.", "status" => false));
}
?>
