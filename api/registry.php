<?php
header("Content-Type: application/json"); // Set response type to JSON
header("Access-Control-Allow-Methods: POST"); // Allow only POST requests

// Enable error reporting to catch any issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/config.php'; // Include database connection

// Get JSON input from request body
$data = json_decode(file_get_contents("php://input"), true);

// Debugging: Show incoming data
echo "Received Data: ";
print_r($data); // This will print the data to help debug

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

// Debugging: Output the extracted data
echo "Nickname: $nickname, Email: $email, Password: $password\n";

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["error" => "Invalid email format"]);
    http_response_code(400);
    exit;
}

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Check if the email already exists
echo "Checking if email exists: $email\n"; // Debugging message
$checkQuery = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$checkQuery->execute([$email]);

// Debugging: Check the number of rows returned from the SELECT query
if ($checkQuery->rowCount() > 0) {
    echo json_encode(["error" => "Email already registered"]);
    http_response_code(409);
    exit;
}

// Insert new user into the database
echo "Inserting new user into the database\n"; // Debugging message
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
