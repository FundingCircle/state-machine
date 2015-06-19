<?php
namespace StateMachine\Tests;

use StateMachine\Accessor\StateAccessor;
use StateMachine\Event\TransitionEvent;
use StateMachine\State\StatefulInterface;
use StateMachine\State\StateInterface;
use StateMachine\StateMachine\StateMachine;
use StateMachine\Tests\Entity\Order;
use StateMachine\Tests\Fixtures\StateMachineFixtures;
use StateMachine\Transition\TransitionInterface;

class GuardsTest extends \PHPUnit_Framework_TestCase
{
    public function testGuardNonExistingTransition()
    {
        $this->setExpectedException("StateMachine\Exception\StateMachineException");
        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->addGuard(
            'pending_refunded',
            function () {
            }
        );
        $stateMachine->boot();
    }

    public function testGuardExistingTransitionWithTrueReturn()
    {
        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->addGuard(
            'pending_checking_out',
            function () {
                //do nothing
            }
        );
        $stateMachine->boot();
        $return = $stateMachine->transitionTo('checking_out');
        $this->assertTrue($return);
        $this->assertEmpty($stateMachine->getMessages());
    }

    public function testGuardExistingTransitionWithFalseReturn()
    {
        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->addGuard(
            'pending_checking_out',
            function (TransitionEvent $transitionEvent) {
                $transitionEvent->addMessage("Transition is rejected by guard");
                $transitionEvent->stopPropagation();
            }
        );
        $stateMachine->boot();
        $return = $stateMachine->transitionTo('checking_out');

        $this->assertFalse($return);
        $this->assertArraySubset(["Transition is rejected by guard"], $stateMachine->getMessages());
    }
}
