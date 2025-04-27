<?php
include_once 'config/config.php';
include_once 'verifyToken.php';

$data = json_decode(file_get_contents("php://input"));
if (!isset($data->attempt_id)) {
    http_response_code(400);
    echo json_encode(array("message" => "Attempt ID is required"));
    exit;
}

$attemptId = (int) $data->attempt_id;

//find user answers for this attempt
$usersAnswers = $conn->prepare("SELECT * FROM anserws WHERE attempt_id = :attempt_id");
$usersAnswers ->bindParam(':attempt_id', $attemptId);
$usersAnswers->execute();
$usersAnswers = $usersAnswers->fetchAll(PDO::FETCH_ASSOC);

//find the quiz id for this attempt
$quizId = $conn->prepare("SELECT quiz_id FROM attempts WHERE attempt_id = :attempt_id");
$quizId->bindParam(':attempt_id', $attemptId);
$quizId->execute();
$quizId = $quizId->fetch(PDO::FETCH_ASSOC);
if (!$quizId) {
    http_response_code(404);
    echo json_encode(array("message" => "Attempt not found"));
    exit;
}
$quizId = $quizId['quiz_id'];

//find the correct answers for this quiz
$correctAnswers = $conn->prepare("SELECT question_id,answer FROM questions WHERE quiz_id = :quiz_id");
$correctAnswers->bindParam(':quiz_id', $quizId);
$correctAnswers->execute();
$correctAnswers = $correctAnswers->fetchAll(PDO::FETCH_ASSOC);
if (!$correctAnswers) {
    http_response_code(404);
    echo json_encode(array("message" => "No questions found for this quiz"));
    exit;
}

$score = 0;
$totalQuestions = count($correctAnswers);

foreach ($usersAnswers as $userAnswer) {
    foreach ($correctAnswers as $correctAnswer) {
        if ($userAnswer['question_id'] == $correctAnswer['question_id']) {
            // Compare answers
            $isCorrect = ($userAnswer['answer'] == $correctAnswer['answer']);
            if ($isCorrect) {
                $score++;
            }
        }
    }
}

// Return the comparison results and score
http_response_code(200);
echo json_encode(array(
    'score' => $score."/".$totalQuestions
));

?>