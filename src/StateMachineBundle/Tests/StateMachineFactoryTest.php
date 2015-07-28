<?php

namespace StateMachineBundle\Tests;

use StateMachineBundle\StateMachine\StateMachineFactory;
use StateMachineBundle\Tests\Entity\Order;

class StateMachineFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testGetUndefinedStateMachine()
    {
        $this->setExpectedException("StateMachine\Exception\StateMachineException");
        $factory = $this->getFactory();
        $factory->get(new Order(1));
    }

    public function testGetStateMachine()
    {
        $definition = [
            'object' => [
                'class' => "StateMachineBundle\Tests\Entity\Order",
                'property' => 'state',
            ],
            'history_class' => "StateMachineBundle\Tests\Entity\History",
            'options' => ['flush' => true],
            'states' => [
                'new' => [
                    'type' => 'initial',
                ],
                'cancelled' => [
                    'type' => 'normal',
                ],
                'originating' => [
                    'type' => 'normal',
                ],
                'committed' => [
                    'type' => 'normal',
                ],
                'error' => [
                    'type' => 'normal',
                ],
                'paid' => [
                    'type' => 'final',
                ],
            ],
            'transitions' => [
                't1' => [
                    'from' => [],
                    'to' => [
                        0 => 'cancelled',
                    ],
                ],
            ],
            'guards' => [
                0 => [
                    'callback' => $this->getMock('StateMachineBundle\Tests\Listeners\MockListener'),
                    'method' => 'callbackMethod',
                    'from' => 'new',
                    'to' => 'cancelled',
                ],
            ],
            'pre_transitions' => [
                0 => [
                    'callback' => $this->getMock('StateMachineBundle\Tests\Listeners\MockListener'),
                    'method' => 'callbackMethod',
                    'from' => 'new',
                    'to' => 'cancelled',
                ],
            ],
            'post_transitions' => [
                0 => [
                    'callback' => $this->getMock('StateMachineBundle\Tests\Listeners\MockListener'),
                    'method' => 'callbackMethod',
                    'from' => 'new',
                    'to' => 'cancelled',
                ],
            ],
        ];

        $factory = $this->getFactory();
        $factory->register($definition);
        $stateMachine = $factory->get(new Order(1));
        $stateMachine->boot();
        $this->assertEquals('new', $stateMachine->getCurrentState()->getName());
    }

    private function getFactory()
    {
        $historyManagerMock = $this->getMockBuilder('StateMachine\History\HistoryManagerInterface')
            ->getMock();

        return new StateMachineFactory($historyManagerMock);
    }
}
