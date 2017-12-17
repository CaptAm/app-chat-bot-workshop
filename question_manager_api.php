<?php
/**
 * Created by PhpStorm.
 * User: Rasa
 * Date: 2017-12-17
 * Time: 15:03
 */

namespace bot;
use GuzzleHttp\ClientInterface;

class question_manager_api implements TriviaProviderInterface
{
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function readTriviaQuestion()
    {
        $res = $this->client->get('https://opentdb.com/api.php?amount=1&difficulty=easy');
        $parse = $res->json();
        $question = $parse["results"]["0"];

  /*    Array
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
        return new question($question["question"], $question["correct_answer"], $question["incorrect_answers"]);
    }
}