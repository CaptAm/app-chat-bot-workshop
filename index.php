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


// FB integracija nuo �ia
include('tokens.php');

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
          [
            "content_type" => "text",
            "title" => "Atsakymas 2",
            "payload" => "ATS2"
          ]
        ] 
    ]
*/
];

if (!array_key_exists($sender,$users) || (strtolower($message)=="restart"))
{
  $users[$sender]["correct"]=0;
  $users[$sender]["incorrect"]=0;
  $users[$sender]["right_answer"]="";
 }
else
{
    if ($users[$sender]["right_answer"]!="")
    {
      if ($message==$users[$sender]["right_answer"])
      {
         $data["message"]["text"]="Teisingai!";
         $users[$sender]["correct"]++;
      }
      else 
      {
         $data["message"]["text"]="Neteisingai :(";
         $users[$sender]["incorrect"]++;
          $response = $fb->post('/me/messages', $data, $access_token);
          $data["message"]["text"]="Teisingas atsakymas:".$users[$sender]["right_answer"];

      }
      $response = $fb->post('/me/messages', $data, $access_token);

      $data["message"]["text"]="Teisingai: ".$users[$sender]["correct"];
      $response = $fb->post('/me/messages', $data, $access_token);

      $data["message"]["text"]="Neteisingai: ".$users[$sender]["incorrect"];
      $response = $fb->post('/me/messages', $data, $access_token);
    }
    $message='Gauti klausima';
}

if ($message=='Gauti klausima')
{
  $data["message"]["text"]=html_entity_decode($question["question"]);
  $answers[]=$question["correct_answer"];
  $users[$sender]["right_answer"]=$question["correct_answer"];

  foreach ($question["incorrect_answers"] as $key => $value) {
      $answers[]=$value;
  }
  shuffle($answers);

  foreach ($answers as $key => $value)
  {
    $qreply["content_type"]="text";
    $qreply["title"]=$value;
    $qreply["payload"]=$value;
    $data["message"]["quick_replies"][]=$qreply;
  }
}
else 
{
  $data["message"]["text"]="Pradedame žaisti?";
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

// I�saugojame userius
$fp = fopen(USERS, 'w');
fwrite($fp, json_encode($users));
fclose($fp);

?>