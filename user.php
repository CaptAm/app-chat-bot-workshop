<?php

namespace bot;

class user
{
    protected $id;
    protected $correct_answers; // Kiek yra teisingų atsakymų
    protected $incorrect_answers; // Kiek yra neteisingų atsakymų
    protected $right_answer; // string - Koks yra paskutinio klausimo teisingas atsakymas

    public function __construct($id, $correct_answers, $incorrect_answers, $right_answer )
    {
        $this->id = $id;
        $this->correct_answers = $correct_answers;
        $this->incorrect_answers = $incorrect_answers;
        $this->right_answer = $right_answer;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCorrectAnswers()
    {
        return $this->correct_answers;
    }

    public function incCorrectAnswers()
    {
        $this->correct_answers++;
    }

    public function getIncorrectAnswers()
    {
        return $this->incorrect_answers;
    }

    public function incIncorrectAnswers()
    {
        $this->incorrect_answers++;
    }

    public function setRightAnswer(string $right_answer)
    {
        $this->right_answer = $right_answer;
    }

    public function getRightAnswer()
    {
        return $this->right_answer;
    }

    public function isRightAnswer(string $right_answer)
    {
        return ($right_answer == $this->right_answer);
    }
}