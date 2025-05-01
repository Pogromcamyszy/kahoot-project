<?php
require 'vendor/autoload.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

include_once 'verifyJwt.php';

$userId = $user['data']->user_id;
$nickname = $user['data']->nickname;

echo json_encode([
    'user_id' => $userId,
    'nickname' => $nickname,
]);
http_response_code(200);
?>
