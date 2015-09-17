<?php

namespace StateMachineBundle\Tests;

use StateMachine\History\HistoryCollection;
use StateMachineBundle\StateMachine\StateMachineFactory;
use StateMachineBundle\Tests\Entity\ChildOrder;
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
        $definition = $this->getDefinition();

        $factory = $this->getFactory();
        $factory->register($definition);
        $stateMachine = $factory->get(new Order(1));
        $stateMachine->boot();
        $this->assertEquals('new', $stateMachine->getCurrentState()->getName());
        $this->assertEquals(1, count($stateMachine->getTransitions()['new->cancelled']->getPostTransitions()));
        $this->assertEquals(1, count($stateMachine->getTransitions()['new->cancelled']->getPreTransitions()));
        $this->assertEquals('order_statemachine', $stateMachine->getName());
    }

    public function testClassWithStatefulParent()
    {
        $definition = [
            'object' => [
                'class' => "StateMachineBundle\Tests\Entity\Order",
                'property' => 'state',
            ],
            'id' => 'order_statemachine',
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
            'transitions' => [],
            'guards' => [],
            'pre_transitions' => [],
            'post_transitions' => [],
        ];

        $factory = $this->getFactory();
        $factory->register($definition);
        $stateMachine = $factory->get(new ChildOrder(1));
        $stateMachine->boot();
        $this->assertEquals('new', $stateMachine->getCurrentState()->getName());
    }

    public function testGetAllDefinitions()
    {
        $definition = $this->getDefinition();
        $factory = $this->getFactory();
        $factory->register($definition);
        $definitions = $factory->getDefinitions();
        $this->assertEquals($definition, reset($definitions));
    }

    public function testGetOneDefinition()
    {
        $definition = $this->getDefinition();
        $factory = $this->getFactory();
        $factory->register($definition);
        $definition = $factory->getDefinition('order_statemachine');
        $this->assertEquals('order_statemachine', $definition['id']);
    }

    public function testGetOneNotFoundDefinition()
    {
        $this->setExpectedException("StateMachine\Exception\StateMachineException");
        $definition = $this->getDefinition();
        $factory = $this->getFactory();
        $factory->register($definition);
        $factory->getDefinition('not_found_statemachine');
    }

    private function getFactory()
    {
        $historyManagerMock = $this->getMockBuilder('StateMachine\History\HistoryManagerInterface')
            ->setMethods(['load', 'add'])
            ->getMock();

        $historyManagerMock->expects($this->any())
            ->method('load')
            ->willReturn(new HistoryCollection());

        return new StateMachineFactory($historyManagerMock);
    }

    private function getDefinition()
    {
        return [
            'id' => 'order_statemachine',
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
                    'callback' => 'test_callback',
                    'method' => 'callbackMethod',
                    'from' => 'new',
                    'to' => 'cancelled',
                ],
            ],
            'pre_transitions' => [
                0 => [
                    'callback' => 'test_callback',
                    'method' => 'callbackMethod_pre',
                    'from' => 'new',
                    'to' => 'cancelled',
                ],
            ],
            'post_transitions' => [
                0 => [
                    'callback' => 'test_callback',
                    'method' => 'callbackMethod_post',
                    'from' => 'new',
                    'to' => 'cancelled',
                ],
            ],
        ];
    }
}
