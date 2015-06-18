<?php
namespace StateMachine\Tests;

use StateMachine\Accessor\StateAccessor;
use StateMachine\State\StateInterface;
use StateMachine\StateMachine\StateMachine;
use StateMachine\Tests\Entity\Order;

class StateMachineTest extends \PHPUnit_Framework_TestCase
{
    public function testAllowedTransitions()
    {
        $stateMachine = $this->getStateMachine();
        $this->assertEquals("pending", $stateMachine->getCurrentState());

        $allowedTransitions = $stateMachine->getAllowedTransitions();

        $this->assertEquals(['checking_out', 'cancelled'], $allowedTransitions);

        $this->assertTrue($stateMachine->canTransitionTo('cancelled'));
        $this->assertFalse($stateMachine->canTransitionTo('shipped'));
    }

    public function testNotAllowedTransition()
    {
        $this->setExpectedException(
            'StateMachine\Exception\StateMachineException',
            "There's no transition defined from (pending) to (shipped), allowed transitions to : [ checking_out,cancelled ]"
        );

        $stateMachine = $this->getStateMachine();
        $stateMachine->transitionTo('shipped');
    }

    private function getStateMachine()
    {
        $class = "StateMachine\Tests\Entity\Order";
        $object = new Order();

        $accessor = new StateAccessor('state');

        $stateMachine = new StateMachine($class, $accessor, $object);

        $stateMachine->addState('pending', StateInterface::TYPE_INITIAL);
        $stateMachine->addState('checking_out');
        $stateMachine->addState('purchased');
        $stateMachine->addState('shipped');
        $stateMachine->addState('cancelled');
        $stateMachine->addState('failed');
        $stateMachine->addState('refunded', StateInterface::TYPE_FINAL);

        $stateMachine->addTransition('pending', 'checking_out');
        $stateMachine->addTransition('pending', 'cancelled');

        $stateMachine->addTransition('checking_out', 'cancelled');
        $stateMachine->addTransition('checking_out', 'purchased');

        $stateMachine->addTransition('purchased', 'failed');
        $stateMachine->addTransition('purchased', 'shipped');

        $stateMachine->addTransition('shipped', 'refunded');

        $stateMachine->boot();

        return $stateMachine;
    }
}
