<?php

include (__DIR__ . '/vendor/autoload.php');

$access_token = 'EAACNqZA0hLBEBAPfGua5rezqGfldRhzWxLR6HdSXP9B98U6x8OZC8QIljXCL0jfrA3Leh1yxNFLm8NYKHEOgZBm1YXuRKjZCmbH649EwTOObJTzvT5D9QVCRMZCY3cZByWsNSWXlNFJN9hZCGRN4V1Gy7HbFRa0nFauhJ4XFZAcmigZDZD';
$verify_token = 'TOKEN';
// $verify_token = 'EAACNqZA0hLBEBAPfGua5rezqGfldRhzWxLR6HdSXP9B98U6x8OZC8QIljXCL0jfrA3Leh1yxNFLm8NYKHEOgZBm1YXuRKjZCmbH649EwTOObJTzvT5D9QVCRMZCY3cZByWsNSWXlNFJN9hZCGRN4V1Gy7HbFRa0nFauhJ4XFZAcmigZDZD';

$appId = '155759625186321';
$appSecret = '1cb2f5e6cff30e93f5b67882245d5e10';

if(isset($_REQUEST['hub_challenge'])) {
    $challenge = $_REQUEST['hub_challenge'];
    if ($_REQUEST['hub_verify_token'] === $verify_token) {
        echo $challenge; die();
    }
}

$input = json_decode(file_get_contents('php://input'), true);

if ($input === null) {
    exit;
}

$message = $input['entry'][0]['messaging'][0]['message']['text'];
$sender = $input['entry'][0]['messaging'][0]['sender']['id'];

$fb = new \Facebook\Facebook([
    'app_id' => $appId,
    'app_secret' => $appSecret,
]);

$data = [
    'messaging_type' => 'RESPONSE',
    'recipient' => [
        'id' => $sender,
    ],
    'message' => [
        'text' => 'You wrote: ' . $message,
    ]
];

$response = $fb->post('/me/messages', $data, $access_token);
