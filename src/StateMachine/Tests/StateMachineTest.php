<?php
namespace StateMachine\Tests;

use StateMachine\Accessor\StateAccessor;
use StateMachine\State\StateInterface;
use StateMachine\StateMachine\StateMachine;
use StateMachine\Tests\Entity\Order;
use StateMachine\Tests\Fixtures\StateMachineFixtures;

class StateMachineTest extends \PHPUnit_Framework_TestCase
{
    public function testTwoInitialStates()
    {
        $this->setExpectedException(
            'StateMachine\Exception\StateMachineException',
            "Statemachine cannot have more than one initial state, current initial state is (pending)"
        );

        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->addState('new_state', StateInterface::TYPE_INITIAL);
    }

    public function testAllowedTransitions()
    {
        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->boot();

        $allowedTransitions = $stateMachine->getAllowedTransitions();

        $this->assertEquals("pending", $stateMachine->getCurrentState());

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

        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->boot();
        $stateMachine->transitionTo('shipped');
    }


}
