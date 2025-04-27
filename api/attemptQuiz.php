<?php 
 include 'config/config.php';
 include_once 'verifyJwt.php';

 $data = json_decode(file_get_contents('php://input'), true);
 $userId = (int) $user['data']->user_id;
 $quizId = (int) $data['quiz_id'];
 $answers = $data['answers'];

 // take the quiz id and grab questions id from database to check if user input is valid
 $questionsToValidate = $conn->prepare("SELECT * FROM questions WHERE quiz_id = $quizId");
 $questionsToValidate->execute();
 $questions = $questionsToValidate->fetchAll(PDO::FETCH_ASSOC);


 // compare user answers with the questions if ids for quiz are correct
 $questionsToCheck = [];
 $answersToCheck = [];
 foreach ($questions as $item){
    $questionsToCheck[] = $item['question_id'];
 }
 foreach ($answers as $item){
    $answersToCheck[] = $item['question_id'];
 }
 $areQuestionsValid = True;
 for($i=0;$i<count($questionsToCheck);$i++){
    if($questionsToCheck[$i] != $answersToCheck[$i]){
        $areQuestionsValid = False;
        break;
    }
 }

 if($areQuestionsValid){
    echo "correct";
 }

 else{
    echo "incorrect";
 }

 ?>