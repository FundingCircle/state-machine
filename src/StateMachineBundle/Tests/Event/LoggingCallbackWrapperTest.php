<?php

namespace StateMachineBundle\Tests\Event;

use PHPUnit\Framework\TestCase;
use StateMachine\Event\TransitionEvent;
use StateMachine\Logger\Logger;
use StateMachineBundle\Event\LoggingCallbackWrapper;

class LoggingCallbackWrapperTest extends TestCase
{
    private $callbackConfig = [
        'callback' => 'callback',
        'method' => 'method',
    ];

    private $eventName = 'eventName';
    private $event;
    private $callback;
    private $callbackMock;

    protected function setUp()
    {
        parent::setUp();
        $this->event = $this->getMockBuilder(TransitionEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->callbackMock = $this->getMockBuilder(\stdClass::class)->setMethods(['fakeCallback'])->getMock();
        $this->callback = [$this->callbackMock, 'fakeCallback'];
    }

    public function testInvokeWithLogger()
    {
        $this->callbackExpectation($this->callbackMock);
        $loggerMock = $this->getMockBuilder(Logger::class)->disableOriginalConstructor()->getMock();
        $loggerMock->expects($this->once())->method('logCallbackCall')->with(
            $this->event,
            $this->eventName,
            $this->callbackConfig,
            'fakeCallbackResult'
        );

        $wrapper = new LoggingCallbackWrapper($this->callbackConfig, $this->callback, $loggerMock);
        $wrapper->__invoke($this->event, $this->eventName);
    }

    public function testInvokeWithoutLogger()
    {
        $this->callbackExpectation($this->callbackMock);
        $wrapper = new LoggingCallbackWrapper($this->callbackConfig, $this->callback);
        $wrapper->__invoke($this->event, $this->eventName);
    }

    public function testToString()
    {
        $wrapper = new LoggingCallbackWrapper($this->callbackConfig, [new \stdClass(), 'fakeMethod']);
        $this->assertSame('stdClass::fakeMethod', $wrapper->__toString());
    }

    private function callbackExpectation($callbackMock)
    {
        $callbackMock->expects($this->once())->method('fakeCallback')->with(
            $this->event,
            $this->eventName
        )->willReturn('fakeCallbackResult');
    }
}
