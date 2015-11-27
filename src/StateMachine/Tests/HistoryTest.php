<?php

namespace StateMachine\Tests;

use StateMachine\Event\TransitionEvent;
use StateMachine\State\StateInterface;
use StateMachine\StateMachine\StateMachine;
use StateMachine\Tests\Fixtures\StateMachineFixtures;
use StateMachineBundle\Tests\Entity\Order;

class HistoryTest extends \PHPUnit_Framework_TestCase
{
    public function testHistoryAfterThreeTransitions()
    {
        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->boot();

        $stateMachine->transitionTo('checking_out');
        $stateMachine->transitionTo('purchased');
        $stateMachine->transitionTo('shipped');

        $this->assertEquals(3, $stateMachine->getHistory()->count());
        $this->assertNotEmpty($stateMachine->getHistory()->toArray());
        $this->assertEquals('purchased', $stateMachine->getLastStateChange()->getFromState());
        $this->assertEquals('shipped', $stateMachine->getLastStateChange()->getToState());
    }

    public function testHistoryWithZeroTransitions()
    {
        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->boot();

        $this->assertEquals(0, count($stateMachine->getHistory()));
        $this->assertNull($stateMachine->getLastStateChange());
    }

    public function testHistoryWithFailedGuard()
    {
        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->addGuard(
            function (TransitionEvent $transitionEvent) {
                return false;
            },
            'pending',
            'checking_out'
        );
        $stateMachine->boot();

        $stateMachine->transitionTo('checking_out');
        $lastTransition = $stateMachine->getLastStateChange();
        $transition = $stateMachine->getHistory()->last();

        $this->assertNull($transition);
        $this->assertNull($lastTransition);
    }

    public function testHistoryWithTwoMovesWithFirstFailed()
    {
        $this->setExpectedException('StateMachine\Exception\StateMachineException');
        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->addGuard(
            function (TransitionEvent $transitionEvent) {
                return false;
            },
            'pending',
            'checking_out'
        );
        $stateMachine->boot();

        $stateMachine->transitionTo('checking_out');
        $stateMachine->transitionTo('purchased');
    }

    public function testHasReachedTrue()
    {
        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->boot();
        $stateMachine->transitionTo('checking_out');
        $stateMachine->transitionTo('purchased');

        $this->assertTrue($stateMachine->hasReached('checking_out'));
        $this->assertTrue($stateMachine->hasReached('purchased'));
        $this->assertFalse($stateMachine->hasReached('failed'));
    }

    public function testHasReachedNoHistory()
    {
        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->boot();

        $this->assertFalse($stateMachine->hasReached('checking_out'));
    }

    public function testSubTransactionChangedToState()
    {
        $stateMachine = new StateMachine(
            new Order(1)
        );

        $stateMachine->addState('A', StateInterface::TYPE_INITIAL);
        $stateMachine->addState('B');
        $stateMachine->addState('C');

        $stateMachine->addTransition('A', 'B');
        $stateMachine->addTransition('A', 'C');

        $stateMachine->addPreTransition(
            function (TransitionEvent $event) {
                $event->setTargetState('C');
            },
            'A',
            'B'
        );
        $stateMachine->boot();

        $stateMachine->transitionTo('B');

        $history = $stateMachine->getHistory();

        $this->assertEquals('C', $stateMachine->getCurrentState()->getName());
        $this->assertEquals(1, $history->count());
        $this->assertEquals($history->first()->getToState(), 'C');
    }
}
