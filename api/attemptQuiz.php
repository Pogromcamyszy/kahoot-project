<?php 
 include 'config/config.php';
 include_once 'verifyJwt.php';
 header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

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

 // if questions are invalid sends response
 if(!$areQuestionsValid)
 {   
      http_response_code(400);
      echo json_encode(array("message" => "Invalid questions IDs"));
      exit;
 }

 // make attempt and insert into attempts table
 try{
   $stmt = $conn->prepare("INSERT INTO attempts (user_id, quiz_id) VALUES (:user_id, :quiz_id)");
   $stmt->bindParam(':user_id', $userId);
   $stmt->bindParam(':quiz_id', $quizId);
   $stmt->execute();
   $attemptId = $conn->lastInsertId();
   echo $attemptId;
 }
 catch(PDOException $e){
   http_response_code(500);
   echo json_encode(array("message" => "Error inserting quiz attempt: " . $e->getMessage()));
   exit;
 }

 try{
   // insert answers into answers table
   $stmt = $conn->prepare("INSERT INTO anserws (attempt_id, question_id, answer) VALUES (:attempt_id, :question_id, :answer)");
   foreach ($answers as $item) {
       $stmt->bindParam(':attempt_id', $attemptId);
       $stmt->bindParam(':question_id', $item['question_id']);
       $stmt->bindParam(':answer', $item['answer']);
       $stmt->execute();
   }
   echo json_encode(array("message" => "Quiz attempt recorded successfully"));
 } catch(PDOException $e) {
   http_response_code(500);
   echo json_encode(array("message" => "Error inserting answers: " . $e->getMessage()));
   exit;
 }
 

 ?>