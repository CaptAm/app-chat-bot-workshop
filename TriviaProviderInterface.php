<?php

namespace bot;

interface TriviaProviderInterface
{
    /**
     * @return question
     */
    public function readTriviaQuestion();
}
