<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
include('config/config.php'); 
include_once 'verifyJwt.php'; 


$quizes = $conn->prepare("SELECT quiz_id, title FROM quizzes");
$quizes->execute();
$quizes = $quizes->fetchAll(PDO::FETCH_ASSOC);
if (!$quizes) {
    http_response_code(404);
    echo json_encode(['error' => 'No quizzes found']);
    exit;
}
else {
    echo json_encode($quizes);
    http_response_code(200);
    exit;
}
?>