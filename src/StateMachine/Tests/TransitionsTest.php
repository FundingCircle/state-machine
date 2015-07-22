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
            "non_existing::non_existing",
            function () {

            }
        );
    }

    public function testPreTransitionWithStopPropagation()
    {
        $stateMachine = StateMachineFixtures::getBidStateMachine();
        $stateMachine->addPreTransition(
            "new::committed",
            function (TransitionEvent $transitionEvent) {
                $transitionEvent->rejectTransition($this);
            }
        );

        $stateMachine->boot();
        $return = $stateMachine->transitionTo("committed");
        $this->assertFalse($return);
    }

    public function testPreTransitionSuccess()
    {
        $stateMachine = StateMachineFixtures::getBidStateMachine();
        $stateMachine->addPreTransition(
            "new::committed",
            function (TransitionEvent $transitionEvent) {
                //do nothing
            }
        );

        $stateMachine->boot();
        $return = $stateMachine->transitionTo("committed");
        $this->assertEquals('committed', $stateMachine->getCurrentState()->getName());
        $this->assertTrue($return);
    }

    public function testPostTransitionOnNonExistingTransition()
    {
        $this->setExpectedException('StateMachine\Exception\StateMachineException');
        $stateMachine = StateMachineFixtures::getBidStateMachine();
        $stateMachine->addPostTransition(
            "non_existing::non_existing",
            function () {

            }
        );
    }

    public function testPostTransitionWithStopPropagation()
    {
        $stateMachine = StateMachineFixtures::getBidStateMachine();
        $stateMachine->addPostTransition(
            "new::committed",
            function (TransitionEvent $transitionEvent) {
                $transitionEvent->rejectTransition($this);
            }
        );

        $stateMachine->boot();
        $return = $stateMachine->transitionTo("committed");
        $this->assertTrue($return);
    }

    public function testPostTransitionSuccess()
    {
        $stateMachine = StateMachineFixtures::getBidStateMachine();
        $stateMachine->addPostTransition(
            "new::committed",
            function (TransitionEvent $transitionEvent) {
                //do nothing
            }
        );

        $stateMachine->boot();
        $return = $stateMachine->transitionTo("committed");
        $this->assertEquals('committed', $stateMachine->getCurrentState()->getName());
        $this->assertTrue($return);
    }

}
