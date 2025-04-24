<?php
header('Content-Type: application/json');
include('config/config.php'); 
include_once 'verifyJwt.php'; 

$userId = (int) $user['data']->user_id;
$data = json_decode(file_get_contents('php://input'), true);

// ✅ Validate input
if (!isset($data['title']) || !isset($data['questions'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Title and questions are required']);
    exit;
}

$title = trim($data['title']);

// Validate title length
if (strlen($title) < 5 || strlen($title) > 100) {
    http_response_code(400);
    echo json_encode(['error' => 'Title must be between 5 and 100 characters']);
    exit;
}

// Insert quiz using prepared statement
try {
    $stmt = $conn->prepare("INSERT INTO quizzes (user_id, title) VALUES (:user_id, :title)");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':title', $title, PDO::PARAM_STR);
    $stmt->execute();

    $quizId = $conn->lastInsertId();

} catch (PDOException $e) {   
        http_response_code(500);
        error_log('Database error: ' . $e->getMessage());
        echo json_encode(['error' => 'Insert failed: ' . $e->getMessage()]);
    }

// Insert questions 
if (isset($data['questions']) && is_array($data['questions'])) {
  try{
    $stmt = $conn->prepare("INSERT INTO questions (quiz_id, question, a, b, c, d, answer) VALUES (:quiz_id, :question, :a, :b, :c, :d, :answer)");

    foreach ($data['questions'] as $question) {
        if (!isset($question['question']) || !isset($question['answer'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Question and answer are required']);
            exit;
        }

        $stmt->bindParam(':quiz_id', $quizId, PDO::PARAM_INT);
        $stmt->bindParam(':question', $question['question'], PDO::PARAM_STR);
        $stmt->bindParam(':a', $question['a'], PDO::PARAM_STR);
        $stmt->bindParam(':b', $question['b'], PDO::PARAM_STR);
        $stmt->bindParam(':c', $question['c'], PDO::PARAM_STR);
        $stmt->bindParam(':d', $question['d'], PDO::PARAM_STR);
        $stmt->bindParam(':answer', $question['answer'], PDO::PARAM_STR);
        $stmt->execute();
    }
} catch (PDOException $e) {
    http_response_code(500);
    error_log('Database error: ' . $e->getMessage());
    echo json_encode(['error' => 'Insert failed: ' . $e->getMessage()]);
    exit;
}
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Questions must be an array']);
    exit;
}

http_response_code(201);
echo json_encode([
    'message' => 'Quiz created successfully',
    'quiz_id' => $quizId,
]);