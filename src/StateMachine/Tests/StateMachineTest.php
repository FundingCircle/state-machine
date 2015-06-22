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
        $stateMachine->addState('another_initial_state', StateInterface::TYPE_INITIAL);
    }

    public function testFromAnyTransition()
    {
        $stateMachine = StateMachineFixtures::getBidStateMachine();
        $stateMachine->addTransition(null, 'cancelled');
        $transitions = $stateMachine->getAllTransitions();
        $this->assertArrayHasKey('new::cancelled', $transitions);
        $this->assertArrayHasKey('error::cancelled', $transitions);
        $this->assertArrayHasKey('committed::cancelled', $transitions);
        $this->assertArrayHasKey('paid::cancelled', $transitions);
        //not to have transition to self
        $this->assertArrayNotHasKey('cancelled::cancelled', $transitions);

    }

    public function testToAnyTransition()
    {
        $stateMachine = StateMachineFixtures::getBidStateMachine();
        $stateMachine->addTransition('paid', null);
        $transitions = $stateMachine->getAllTransitions();
        $this->assertArrayHasKey('paid::new', $transitions);
        $this->assertArrayHasKey('paid::cancelled', $transitions);
        $this->assertArrayHasKey('paid::originating', $transitions);
        $this->assertArrayHasKey('paid::committed', $transitions);
        $this->assertArrayHasKey('paid::error', $transitions);
        //not to have transition to self
        $this->assertArrayNotHasKey('paid::paid', $transitions);

    }

    public function testFromManyTransitions()
    {
        $stateMachine = StateMachineFixtures::getBidStateMachine();
        $stateMachine->addState('from_many');
        $stateMachine->addTransition(['paid', 'originating'], 'from_many');
        $transitions = $stateMachine->getAllTransitions();
        $this->assertArrayHasKey('originating::from_many', $transitions);
        $this->assertArrayHasKey('paid::from_many', $transitions);
    }

    public function testToManyTransitions()
    {
        $stateMachine = StateMachineFixtures::getBidStateMachine();
        $stateMachine->addTransition('paid', ['new', 'originating']);
        $transitions = $stateMachine->getAllTransitions();
        $this->assertArrayHasKey('paid::new', $transitions);
        $this->assertArrayHasKey('paid::originating', $transitions);
    }

    public function testFromManyToAll()
    {
        $stateMachine = StateMachineFixtures::getBidStateMachine();
        $stateMachine->addState('source1');
        $stateMachine->addState('source2');
        $stateMachine->addTransition(['source1', 'source2'], null);
        $transitions = $stateMachine->getAllTransitions();
        $this->assertArrayHasKey('source1::new', $transitions);
        $this->assertArrayHasKey('source2::new', $transitions);

        $this->assertArrayHasKey('source1::cancelled', $transitions);
        $this->assertArrayHasKey('source2::cancelled', $transitions);

        $this->assertArrayHasKey('source1::originating', $transitions);
        $this->assertArrayHasKey('source2::originating', $transitions);

        $this->assertArrayHasKey('source1::committed', $transitions);
        $this->assertArrayHasKey('source2::committed', $transitions);

        $this->assertArrayHasKey('source1::error', $transitions);
        $this->assertArrayHasKey('source2::error', $transitions);

        $this->assertArrayHasKey('source1::paid', $transitions);
        $this->assertArrayHasKey('source2::paid', $transitions);
    }

    public function testFromAllToMany()
    {
        $stateMachine = StateMachineFixtures::getBidStateMachine();
        $stateMachine->addState('destination1');
        $stateMachine->addState('destination2');
        $stateMachine->addTransition(null, ['destination1', 'destination2']);
        $transitions = $stateMachine->getAllTransitions();
        $this->assertArrayHasKey('new::destination1', $transitions);
        $this->assertArrayHasKey('new::destination2', $transitions);

        $this->assertArrayHasKey('cancelled::destination1', $transitions);
        $this->assertArrayHasKey('cancelled::destination2', $transitions);

        $this->assertArrayHasKey('originating::destination1', $transitions);
        $this->assertArrayHasKey('originating::destination2', $transitions);

        $this->assertArrayHasKey('committed::destination1', $transitions);
        $this->assertArrayHasKey('committed::destination2', $transitions);

        $this->assertArrayHasKey('error::destination1', $transitions);
        $this->assertArrayHasKey('error::destination2', $transitions);

        $this->assertArrayHasKey('paid::destination1', $transitions);
        $this->assertArrayHasKey('paid::destination2', $transitions);
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

    public function testTransitionTo()
    {
        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->boot();
        $stateMachine->transitionTo('checking_out');
        $this->assertEquals($stateMachine->getCurrentState(), 'checking_out');
    }
}
