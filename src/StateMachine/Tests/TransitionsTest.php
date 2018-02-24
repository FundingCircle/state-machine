<?php

namespace StateMachine\Tests;

use PHPUnit\Framework\TestCase;
use StateMachine\Event\TransitionEvent;
use StateMachine\Exception\StateMachineException;
use StateMachine\Tests\Fixtures\StateMachineFixtures;

class TransitionsTest extends TestCase
{
    public function testPreTransitionOnNonExistingTransition()
    {
        $this->expectException(StateMachineException::class);
        $stateMachine = StateMachineFixtures::getBidStateMachine();
        $stateMachine->addPreTransition(
            function () {
            },
            'non_existing',
            'non_existing'
        );
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
        $this->expectException(StateMachineException::class);
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
                return false;
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

    public function testPostCommitCalled()
    {
        $stateMachine = StateMachineFixtures::getBidStateMachine();
        $stateMachine->addPostCommit(
            function (TransitionEvent $transitionEvent) {
                //do nothing
                $transitionEvent->addMessage('post commit called');
            },
            'new',
            'committed'
        );

        $stateMachine->boot();
        $return = $stateMachine->transitionTo('committed');
        $this->assertEquals('committed', $stateMachine->getCurrentState()->getName());
        $this->assertTrue($return);
        $this->assertEquals([0 => 'post commit called'], $stateMachine->getMessages());
    }
}
