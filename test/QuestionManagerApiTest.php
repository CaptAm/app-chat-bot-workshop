<?php
namespace bot\test;

use bot\question;
use bot\question_manager_api;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ParseException;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QuestionManagerApiTest extends TestCase
{
    public function testReadTriviaQuestion()
    {
        $json = '{"response_code":0,"results":[{"category":"Science: Computers","type":"multiple","difficulty":"easy","question":"What does GHz stand for?","correct_answer":"Gigahertz","incorrect_answers":["Gigahotz","Gigahetz","Gigahatz"]}]}';
        $questionManagerApi = $this->getQuestionManager($json);

        $question = $questionManagerApi->readTriviaQuestion();
        $this->assertInstanceOf(question::class, $question);
        $this->assertSame('Gigahertz', $question->getRightAnswer());
    }

    public function testReadTriviaQuestionGivesNoQuestion()
    {
        $json = 'kjasdkjs';
        $questionManagerApi = $this->getQuestionManager($json);
        $this->expectException(ParseException::class);

        $questionManagerApi->readTriviaQuestion();
    }

    private function getQuestionManager($body)
    {
        $response = new Response(200, [], Stream::factory($body));

        /** @var ClientInterface|MockObject $mock */
        $mock = $this->getMockBuilder(ClientInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $mock
            ->expects($this->any())
            ->method('get')
            ->willReturn($response)
        ;
        return new question_manager_api($mock);
    }
}