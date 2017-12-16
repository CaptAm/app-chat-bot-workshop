<?php

const USERS="users.json";
include (__DIR__ . '/vendor/autoload.php');

// Užkrauname userius
if (file_exists(USERS))
{
  $users = json_decode(file_get_contents(USERS),true);
}

// Testuojam Trivia
$client = new GuzzleHttp\Client();
$res = $client->get('https://opentdb.com/api.php?amount=1&difficulty=easy');
// echo $res->getStatusCode();
// "200"
// echo $res->getHeader('content-type');
// 'application/json; charset=utf8'
//echo $res->getBody();
// {"type":"User"...'
//echo "<hr>";
// $parse=json_decode($res->get(), true);

 $parse=$res->json();
 $question=$parse["results"]["0"];
// Outputs the JSON decoded data

/*
echo "<pre>";
print_r($question);
echo "</pre>";

Array
(
    [category] => Geography
    [type] => multiple
    [difficulty] => easy
    [question] => Which nation claims ownership of Antarctica?
    [correct_answer] => No one, but there are claims.
    [incorrect_answers] => Array
        (
            [0] => United States of America
            [1] => United Nations
            [2] => Australia
        )

)
*/


// FB integracija nuo èia

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
/*    'message' => [
        'text' => 'You wrote: ' . $message,
        'quick_replies' => [
          [
            "content_type" => "text",
            "title" => "Atsakymas 1",
            "payload" => "ATS1"
          ],
          [
            "content_type" => "text",
            "title" => "Atsakymas 2",
            "payload" => "ATS2"
          ]
        ] 
    ]
*/
];

$logged_user="";
foreach ($users as $user_id => $user_data)
{
  if ($user_data["sender"] == $sender)
  { 
    $logged_user=$user_id; 
  }
}

if ($logged_user=="")
{
// tODO: I VIENA IRASA
  $users[]["sender"]=$sender;
  $users[]["correct"]=0;
  $users[]["incorrect"]=0;

  // Cia reikia $logged_user nustatyti i paskutini elementa
  foreach ($users as $user_id => $user_data)
  {
    if ($user_data["sender"] == $sender)
    { $logged_user=$user_id; }
  }
}
else 
{
    if ($message==$users[$logged_user]["right_answer"])
    {
       $data["message"]["text"]="Teisingai!";
       $users[$logged_user]["correct"]++;
    }
    else 
    {
       $data["message"]["text"]="Neteisingai :(";
       $users[$logged_user]["incorrect"]++;
    }
    $response = $fb->post('/me/messages', $data, $access_token);

    $data["message"]["text"]="Teisingai: ".$users[$logged_user]["correct"];
    $response = $fb->post('/me/messages', $data, $access_token);

    $data["message"]["text"]="Neteisingai: ".$users[$logged_user]["incorrect"];
    $response = $fb->post('/me/messages', $data, $access_token);

    $message='Gauti klausima';
}

if ($message=='Gauti klausima')
{
  $data["message"]["text"]=html_entity_decode($question["question"]);

  $qreply["content_type"]="text";
  $qreply["title"]=$question["correct_answer"];
  $qreply["payload"]=$question["correct_answer"];
  $data["message"]["quick_replies"][]=$qreply;
  $users[$logged_user]["right_answer"]=$question["correct_answer"];

  foreach ($question["incorrect_answers"] as $key => $value)
  {
    $qreply["content_type"]="text";
    $qreply["title"]=$value;
    $qreply["payload"]=$value;
    $data["message"]["quick_replies"][]=$qreply;
  }
}
else 
{
  $data["message"]["text"]="Pradedam zaisti?";
  $qreply["content_type"]="text";
  $qreply["title"]="Gauti klausima";
  $qreply["payload"]="Klausimas";
  $data["message"]["quick_replies"][]=$qreply;
}

/*
echo "<pre>";
print_r($data);
echo "</pre>";


/*
echo "<pre>";
print_r($question);
echo "</pre>";
echo "<hr>";

echo "Kategorija: ".$question["category"]."<br>";
echo "Sunkumas: ".$question["difficulty"]."<br>";
echo "Klausimas: <b>".$question["question"]."</b><br>";

$questions[]=$question;
echo("<pre>");
print_r($users);
echo("<hr>");
*/

$response = $fb->post('/me/messages', $data, $access_token);

// Išsaugojame userius
$fp = fopen(USERS, 'w');
fwrite($fp, json_encode($users));
fclose($fp);

?>
