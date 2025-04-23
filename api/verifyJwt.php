
<?php
require 'vendor/autoload.php'; // Make sure this path is correct

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

include 'secret.php'; 

function getAuthorizationHeader() {
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            return trim($headers['Authorization']);
        }
    }

    // Fallback if getallheaders doesn't work
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        return trim($_SERVER['HTTP_AUTHORIZATION']);
    }

    return null;
}

$authHeader = getAuthorizationHeader();

if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized: Bearer token not found"]);
    exit;
}

$jwt = $matches[1];

try {
    $decoded = JWT::decode($jwt, new Key($jwtSecretKey , 'HS256'));
    $user = (array) $decoded; // You now have the payload, e.g. user info
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid token: " . $e->getMessage()]);
    exit;
}
