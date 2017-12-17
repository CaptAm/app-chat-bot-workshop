<?php

namespace bot;

class question
{
    protected $question;
    protected $right_answer;
    protected $incorrect_answers;

    public function __construct($question, $right_answer, $incorrect_answers)
    {
        $this->question = html_entity_decode($question, ENT_QUOTES);
        $this->right_answer = html_entity_decode($right_answer, ENT_QUOTES);
        foreach ($incorrect_answers as $key => $value)
        {
            $encoded_incorrect_answers[$key]=html_entity_decode($value, ENT_QUOTES);
        }
        $this->incorrect_answers = $encoded_incorrect_answers;
    }

    public function getQuestion()
    {
        return $this->question;
    }

    public function getRightAnswer()
    {
        return $this->right_answer;
    }

    public function getIncorrectAnswers()
    {
        return $this->incorrect_answers;
    }
}