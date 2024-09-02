<?php
//Skripta služi za generiranje JWT žetona za strežnik ki pošilja podatke o porabi energije

// Prikaz napak
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Dodaj autoloader
require __DIR__ . '/../vendor/autoload.php'; 
use \Firebase\JWT\JWT;


$secret_key = "moja_skrivnost"; 
$issued_at = time(); // Time token issued
$expiration_time = $issued_at + (3600*24*60); // Token expiration time - 60 days
$user_id = 1101; // Example user ID

$payload = [
    "iat" => $issued_at,
    "exp" => $expiration_time,
    "data" => [
        "user_id" => $user_id,
    ]
];

//$jwt = JWT::encode($payload, $secret_key, 'HS256');

echo json_encode([
    "token" => $jwt,
    "expires_in" => $expiration_time
]);
?>