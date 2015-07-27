<?php

namespace StateMachine\Tests;

use StateMachine\Event\TransitionEvent;
use StateMachine\Tests\Fixtures\StateMachineFixtures;

class GuardsTest extends \PHPUnit_Framework_TestCase
{
    public function testGuardNonExistingTransition()
    {
        $this->setExpectedException("StateMachine\Exception\StateMachineException");
        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->addGuard(
            'pending->refunded',
            function () {
            }
        );
        $stateMachine->boot();
    }

    public function testGuardExistingTransitionWithTrueReturn()
    {
        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->addGuard(
            'pending->checking_out',
            function () {
                return true;
            }
        );
        $stateMachine->boot();
        $return = $stateMachine->transitionTo('checking_out');
        $this->assertTrue($return);
        $this->assertEmpty($stateMachine->getLastStateChange()->getMessages());
    }

    public function testGuardExistingTransitionWithFalseReturn()
    {
        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->addGuard(
            'pending->checking_out',
            function (TransitionEvent $transitionEvent) {
                $transitionEvent->addMessage('Transition is rejected by guard');

                return false;
            }
        );
        $stateMachine->boot();
        $return = $stateMachine->transitionTo('checking_out');

        $this->assertFalse($return);
        $this->assertArraySubset(
            ['Transition is rejected by guard'],
            $stateMachine->getLastStateChange()->getMessages()
        );
    }

    public function testMultiStateMachineWithOneGuard()
    {
        $stateMachine1 = StateMachineFixtures::getOrderStateMachine();
        $stateMachine2 = StateMachineFixtures::getOrderStateMachine();

        $stateMachine1->addGuard(
            'pending->checking_out',
            function (TransitionEvent $transitionEvent) {
                $transitionEvent->addMessage('Transition is rejected by guard');

                return false;
            }
        );
        $stateMachine1->boot();
        $stateMachine2->boot();

        $this->assertTrue($stateMachine2->canTransitionTo('checking_out'));
        $this->assertTrue($stateMachine2->transitionTo('checking_out'));

        $this->assertTrue($stateMachine1->canTransitionTo('checking_out'));
        $this->assertFalse($stateMachine1->canTransitionTo('checking_out', true));
        $this->assertFalse($stateMachine1->transitionTo('checking_out'));
    }
}
