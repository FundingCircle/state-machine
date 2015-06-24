<?php

namespace StateMachineBundle\Tests;

use StateMachineBundle\StateMachine\StateMachineFactory;
use StateMachineBundle\Tests\Entity\Order;
use Symfony\Component\EventDispatcher\EventDispatcher;

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
            "class"       => "StateMachineBundle\Tests\Entity\Order",
            "property"    => "state",
            "states"      => [
                "new"         => [
                    "type" => "initial"
                ],
                "cancelled"   => [
                    "type" => "normal"
                ],
                "originating" => [
                    "type" => "normal"
                ],
                "committed"   => [
                    "type" => "normal"
                ],
                "error"       => [
                    "type" => "normal"
                ],
                "paid"        => [
                    "type" => "final"
                ],
            ],
            "transitions" => [
                "t1" => [
                    "from"             => [],
                    "to"               => [
                        0 => "cancelled",
                    ],
                    "guards"           => [
                        0 => [
                            "callback" => $this->getMock('StateMachineBundle\Tests\Listeners\MockListener'),
                            "method"   => "callbackMethod",
                        ],
                    ],
                    "pre_transitions"  => [
                        0 => [
                            "callback" => $this->getMock('StateMachineBundle\Tests\Listeners\MockListener'),
                            "method"   => "callbackMethod",
                        ],
                    ],
                    "post_transitions" => [
                        0 => [
                            "callback" => $this->getMock('StateMachineBundle\Tests\Listeners\MockListener'),
                            "method"   => "callbackMethod",
                        ],
                    ]
                ]
            ]
        ];

        $factory = $this->getFactory();
        $factory->register($definition);
        $stateMachine = $factory->get(new Order(1));
        $this->assertEquals('new', $stateMachine->getCurrentState()->getName());
    }

    private function getFactory()
    {
        $eventDispatcherMock = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcher')
            ->disableOriginalConstructor()
            ->getMock();

        $eventDispatcherMock->expects($this->any())
            ->method('addListener');

        return new StateMachineFactory($eventDispatcherMock);
    }
}
