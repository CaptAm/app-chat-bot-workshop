<?php

namespace bot;

class question_manager_file implements TriviaProviderInterface
{
    const QUESTIONS = "questions.csv";

    public function readTriviaQuestion()
    {
        $n = 0;
        $file = fopen(self::QUESTIONS, 'r');
        while (($line = fgetcsv($file)) !== FALSE) {
            $n++;
        }
        fclose($file);

        $r=random_int(1,$n);
        $n = 0;
        $file = fopen(self::QUESTIONS, 'r');
        while (($line = fgetcsv($file)) !== FALSE) {
            $n++;
            if ($n==$r) { $packed=$line; }
        }
        fclose($file);

        $question=$packed[0];
        $correct_answer=$packed[1];
        unset($packed[0]);
        unset($packed[1]);

        return new question($question, $correct_answer, $packed);
    }
}