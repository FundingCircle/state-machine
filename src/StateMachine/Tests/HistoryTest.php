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
        $this->assertEquals('purchased::shipped', $stateMachine->getLastTransition()->getName());
    }

    public function testHistoryWithZeroTransitions()
    {
        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->boot();

        $this->assertEquals(0, count($stateMachine->getHistory()));
        $this->assertNull($stateMachine->getLastTransition());
    }

    public function testHistoryWithFailedGuard()
    {
        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->addGuard(
            'pending::checking_out',
            function (TransitionEvent $transitionEvent) {
                $transitionEvent->rejectTransition($this);
            }
        );
        $stateMachine->boot();

        $stateMachine->transitionTo('checking_out');
        $lastTransition = $stateMachine->getLastTransition();
        $statChange = $stateMachine->getHistory()->last();

        $this->assertFalse($statChange->isPassed());
        $this->assertEquals(1, $statChange->getIdentifier());
        $this->assertEquals(1, $stateMachine->getHistory()->count());
        $this->assertEquals('pending::checking_out', $lastTransition->getName());
    }

    public function testHistoryWithTwoMovesWithFirstFailed()
    {
        $this->setExpectedException('StateMachine\Exception\StateMachineException');
        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->addGuard(
            'pending::checking_out',
            function (TransitionEvent $transitionEvent) {
                $transitionEvent->rejectTransition($this);
            }
        );
        $stateMachine->boot();

        $stateMachine->transitionTo('checking_out');
        $stateMachine->transitionTo('purchased');
    }
}
