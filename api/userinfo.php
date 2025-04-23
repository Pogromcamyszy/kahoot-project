<?php
require 'vendor/autoload.php';

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
