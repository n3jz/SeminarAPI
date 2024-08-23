<?php
// Display all errors (for debugging purposes)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include Composer's autoloader
require __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;


$secret_key = "moja_skrivnost"; // Use a strong secret key
$issuer = "yourdomain.com"; // The issuer of the token
$audience = "yourdomain.com"; // The audience of the token
$issued_at = time(); // The current time when the token is issued
$expiration_time = $issued_at + (3600*24*60); // Token expiration time - 60 days
$user_id = 123; // Example user ID

$payload = [
    "iss" => $issuer,
    "aud" => $audience,
    "iat" => $issued_at,
    "exp" => $expiration_time,
    "data" => [
        "user_id" => $user_id,
        // Add other user-specific data if needed
    ]
];

$jwt = JWT::encode($payload, $secret_key, 'HS256');

echo json_encode([
    "token" => $jwt,
    "expires_in" => $expiration_time
]);
?>