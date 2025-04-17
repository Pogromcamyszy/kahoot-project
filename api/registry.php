<?php
header("Content-Type: application/json"); // Set response type to JSON
header("Access-Control-Allow-Methods: POST"); // Allow only POST requests

// Enable error reporting to catch any issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/config.php'; // Include database connection

// Get JSON input from request body
$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (!isset($data['nickname']) || !isset($data['email']) || !isset($data['password'])) {
    echo json_encode(["error" => "All fields (nickname, email, password) are required"]);
    http_response_code(400);
    exit;
}

// Extract and sanitize input
$nickname = trim($data['nickname']);
$email = trim($data['email']);
$password = trim($data['password']);

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["error" => "Invalid email format"]);
    http_response_code(400);
    exit;
}

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Check if the email already exists
$checkQuery = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$checkQuery->execute([$email]);

// Debugging: Check the number of rows returned from the SELECT query
if ($checkQuery->rowCount() > 0) {
    echo json_encode(["error" => "Email already registered"]);
    http_response_code(409);
    exit;
}

$checkNick = $conn->prepare("SELECT user_id FROM users WHERE nickname = ?");
$checkNick->execute([$nickname]);

if ($checkNick->rowCount() > 0) {
    echo json_encode(["error" => "Nickname already taken"]);
    http_response_code(409);
    exit;
}

// Insert new user into the database
$query = "INSERT INTO users (nickname, email, password) VALUES (?, ?, ?)";
$stmt = $conn->prepare($query);

if ($stmt->execute([$nickname, $email, $hashedPassword])) {
    echo json_encode(["success" => "User registered successfully"]);
    http_response_code(201);
} else {
    echo json_encode(["error" => "Error inserting user into database"]);
    http_response_code(500);
}

?>
