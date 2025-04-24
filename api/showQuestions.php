<?php
header('Content-Type: application/json');
include('config/config.php'); 
include_once 'verifyJwt.php'; 


// Read JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Validate input: Check if quiz_id is provided
if (!isset($data['quiz_id']) || !is_numeric($data['quiz_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'quiz_id is required and must be numeric']);
    exit;
}

$quizId = (int) $data['quiz_id'];

try {
    // Fetch all questions for the given quiz_id
    $stmt = $conn->prepare("SELECT question_id,question, a, b, c, d FROM questions WHERE quiz_id = :quiz_id");
    $stmt->bindParam(':quiz_id', $quizId, PDO::PARAM_INT);
    $stmt->execute();

    // Get the result as an associative array
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($questions) {
        // If questions are found, return them in the response
        http_response_code(200);
        echo json_encode(['questions' => $questions]);
    } else {
        // If no questions are found for the quiz_id
        http_response_code(404);
        echo json_encode(['error' => 'No questions found for this quiz']);
    }

} catch (PDOException $e) {
    // Handle database connection or query errors
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>