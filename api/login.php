<?php
require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

include 'config/config.php'; 

header('Content-Type: application/json');

// Secret keys
include 'secret.php'; 



// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$nickname = $input['nickname'] ?? '';
$password = $input['password'] ?? '';

// Validate input
if (empty($nickname) || empty($password)) {
    http_response_code(400);
    echo json_encode(['error' => 'nickname and password required']);
    exit;
}

// Query DB for user
$stmt = $conn->prepare("SELECT user_id,password FROM users WHERE nickname = ?");
$stmt->execute([$nickname]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
// Check if user exists and password is correct
if (!$user || !password_verify($password, $user['password'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid nickname or password']);
    exit;
}

// Generate JWT access token
$issuedAt = time();
$expirationTime = $issuedAt + 36000; // 1 hour
$payload = [
    'iss' => 'localhost',
    'iat' => $issuedAt,
    'exp' => $expirationTime,
    'data' => [
        'nickname' => $nickname,
        'user_id' => $user['user_id']
    ]
];

$jwt = JWT::encode($payload, $jwtSecretKey, 'HS256');

// Generate refresh token
$refreshPayload = [
    'iss' => 'localhost',
    'iat' => $issuedAt,
    'exp' => $issuedAt + 604800, // 7 days
    'sub' => $nickname
];
$refreshToken = JWT::encode($refreshPayload, $refreshTokenSecret, 'HS256');

// Send response
echo json_encode([
    'access_token' => $jwt,
    'refresh_token' => $refreshToken,
    'expires_in' => 3600
]);
