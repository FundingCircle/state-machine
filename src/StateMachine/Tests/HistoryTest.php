<?php

namespace StateMachine\Tests;

use StateMachine\Event\TransitionEvent;
use StateMachine\Tests\Fixtures\StateMachineFixtures;

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
                $transitionEvent->rejectTransition($this);
            },
            'pending',
            'checking_out'
        );
        $stateMachine->boot();

        $stateMachine->transitionTo('checking_out');
        $lastTransition = $stateMachine->getLastStateChange();
        $transition = $stateMachine->getHistory()->last();

        $this->assertFalse($transition->isPassed());
        $this->assertEquals(1, $transition->getObjectIdentifier());
        $this->assertEquals(1, $stateMachine->getHistory()->count());
        $this->assertEmpty($transition->getPreTransitions());
        $this->assertEmpty($transition->getPostTransitions());
        $this->assertEmpty($transition->getMessages());
        $this->assertEquals(1, count($transition->getGuards()));
        $this->assertNotNull($transition->getFailedCallBack());
        $this->assertEquals(1, $stateMachine->getHistory()->first()->getObjectIdentifier());
        $this->assertEquals('pending', $lastTransition->getFromState());
        $this->assertEquals('checking_out', $lastTransition->getToState());
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
}
