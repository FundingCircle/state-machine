<?php
namespace StateMachine\Tests;

use StateMachine\Accessor\StateAccessor;
use StateMachine\State\StateInterface;
use StateMachine\StateMachine\StateMachine;
use StateMachine\Tests\Entity\Order;
use StateMachine\Tests\Fixtures\StateMachineFixtures;

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

    public function testGuardExistingTransitionWithNonBooleanReturn()
    {
        $this->setExpectedException("StateMachine\Exception\StateMachineException");
        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->addGuard(
            'pending_checking_out',
            function () {
            }
        );
        $stateMachine->boot();
        $stateMachine->transitionTo('checking_out');
    }

    public function testGuardExistingTransitionWithTrueReturn()
    {
        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->addGuard(
            'pending_checking_out',
            function () {
                return true;
            }
        );
        $stateMachine->boot();
        $return = $stateMachine->transitionTo('checking_out');
        $this->assertTrue($return);
    }

    public function testGuardExistingTransitionWithFalseReturn()
    {
        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->addGuard(
            'pending_checking_out',
            function () {
                return false;
            }
        );
        $stateMachine->boot();
        $return = $stateMachine->transitionTo('checking_out');
        $this->assertFalse($return);
    }
}
