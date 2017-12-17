<?php

use bot\user;
use bot\user_manager;
use bot\question;
use bot\question_manager_api;
use bot\question_manager_file;

include(__DIR__ . '/vendor/autoload.php');

$userManager=new user_manager();
/** @var \bot\TriviaProviderInterface $questionManager */
// $questionManager = new question_manager_api();
$questionManager = new question_manager_file();

// FB integracija nuo �ia
include('tokens.php');

if (isset($_REQUEST['hub_challenge'])) {
    $challenge = $_REQUEST['hub_challenge'];
    if ($_REQUEST['hub_verify_token'] === $verify_token) {
        echo $challenge;
        die();
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
    /*    'message' => [
            'text' => 'You wrote: ' . $message,
            'quick_replies' => [
              [
                "content_type" => "text",
                "title" => "Atsakymas 1",
                "payload" => "ATS1"
              [
                "content_type" => "text",
                "title" => "Atsakymas 2",
                "payload" => "ATS2"
              ]
            ]
        ]
    */
];

$user=$userManager->getUser($sender);

if (($user==NULL) || (strtolower($message) == "restart")) {
    $user=new user($sender, 0, 0, "");
} else {
    if ($user->getRightAnswer() != "") {
        if ($user->isRightAnswer($message)) {
            $data["message"]["text"] = "Teisingai!";
            $user->incCorrectAnswers();
        } else {
            $data["message"]["text"] = "Neteisingai :(";
            $user->incIncorrectAnswers();
            $response = $fb->post('/me/messages', $data, $access_token);
            $data["message"]["text"] = "Teisingas atsakymas:" . $user->getRightAnswer();
        }
        $response = $fb->post('/me/messages', $data, $access_token);

        $data["message"]["text"] = "Teisingai: " . $user->getCorrectAnswers();
        $response = $fb->post('/me/messages', $data, $access_token);

        $data["message"]["text"] = "Neteisingai: " . $user->getIncorrectAnswers();
        $response = $fb->post('/me/messages', $data, $access_token);
    }
    $message = 'Gauti klausima';
}

if ($message == 'Gauti klausima') {
    $question=$questionManager->readTriviaQuestion();

    $data["message"]["text"] = $question->getQuestion();
    $answers[] = $question->getRightAnswer();
    $user->setRightAnswer($question->getRightAnswer());

    foreach ($question->getIncorrectAnswers() as $key => $value) {
        $answers[] = $value;
    }
    shuffle($answers);

    foreach ($answers as $key => $value) {
        $qreply["content_type"] = "text";
        $qreply["title"] = $value;
        $qreply["payload"] = $value;
        $data["message"]["quick_replies"][] = $qreply;
    }
} else {
    $data["message"]["text"] = "Pradedame žaisti?";
    $qreply["content_type"] = "text";
    $qreply["title"] = "Gauti klausima";
    $qreply["payload"] = "Klausimas";
    $data["message"]["quick_replies"][] = $qreply;
}

$response = $fb->post('/me/messages', $data, $access_token);

$userManager->saveUser($user);

?>