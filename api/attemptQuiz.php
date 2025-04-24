<?php 
 include 'config/config.php';
 include_once 'verifyJwt.php';

 $data = json_decode(file_get_contents('php://input'), true);
 $userId = (int) $user['data']->user_id;
 echo json_encode($userId);
 ?>