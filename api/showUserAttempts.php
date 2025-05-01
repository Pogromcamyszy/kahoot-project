<?php
include 'config/config.php';
include_once 'verifyJwt.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

$userId = (int) $user['data']->user_id;

$stmt = $conn->prepare("
    SELECT q.title, a.attempt_id, a.date
    FROM attempts a
    INNER JOIN quizzes q ON a.quiz_id = q.quiz_id
    WHERE a.user_id = :user_id
");
$stmt->bindParam(':user_id', $userId);
$stmt->execute();
$attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (empty($attempts)) {
    http_response_code(404);
    echo json_encode(array("message" => "No attempts found"));
    exit;
}

http_response_code(200);
echo json_encode($attempts);


?>