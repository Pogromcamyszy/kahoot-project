<?php
header('Content-Type: application/json');
include('db.php');
include_once 'verifyJwt.php'; 

if(isset($user['data']->user_id)) {
    $userId = $user['data']->user_id;
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

echo json_encode(['user_id' => $userId]);
http_response_code(200);