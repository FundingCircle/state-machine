<?php

namespace StateMachine\Tests;

use StateMachine\Event\TransitionEvent;
use StateMachine\Tests\Fixtures\StateMachineFixtures;

class TransitionsTest extends \PHPUnit_Framework_TestCase
{
    public function testPreTransitionOnNonExistingTransition()
    {
        $this->setExpectedException('StateMachine\Exception\StateMachineException');
        $stateMachine = StateMachineFixtures::getBidStateMachine();
        $stateMachine->addPreTransition(
            function () {

            },
            'non_existing',
            'non_existing'
        );
    }

    public function testPreTransitionWithStopPropagation()
    {
        $stateMachine = StateMachineFixtures::getBidStateMachine();
        $stateMachine->addPreTransition(
            function (TransitionEvent $transitionEvent) {
                return false;
            },
            'new',
            'committed'
        );

        $stateMachine->boot();
        $return = $stateMachine->transitionTo('committed');
        $this->assertFalse($return);
    }

    public function testPreTransitionSuccess()
    {
        $stateMachine = StateMachineFixtures::getBidStateMachine();
        $stateMachine->addPreTransition(
            function (TransitionEvent $transitionEvent) {
                return true;
            },
            'new',
            'committed'
        );

        $stateMachine->boot();
        $return = $stateMachine->transitionTo('committed');
        $this->assertEquals('committed', $stateMachine->getCurrentState()->getName());
        $this->assertTrue($return);
    }

    public function testPostTransitionOnNonExistingTransition()
    {
        $this->setExpectedException('StateMachine\Exception\StateMachineException');
        $stateMachine = StateMachineFixtures::getBidStateMachine();
        $stateMachine->addPostTransition(
            function () {

            },
            'non_existing',
            'non_existing'
        );
    }

    public function testPostTransitionWithStopPropagation()
    {
        $stateMachine = StateMachineFixtures::getBidStateMachine();
        $stateMachine->addPostTransition(
            function (TransitionEvent $transitionEvent) {
                $transitionEvent->rejectTransition($this);
            },
            'new',
            'committed'
        );

        $stateMachine->boot();
        $return = $stateMachine->transitionTo('committed');
        $this->assertTrue($return);
    }

    public function testPostTransitionSuccess()
    {
        $stateMachine = StateMachineFixtures::getBidStateMachine();
        $stateMachine->addPostTransition(
            function (TransitionEvent $transitionEvent) {
                //do nothing
            },
            'new',
            'committed'
        );

        $stateMachine->boot();
        $return = $stateMachine->transitionTo('committed');
        $this->assertEquals('committed', $stateMachine->getCurrentState()->getName());
        $this->assertTrue($return);
    }
}
