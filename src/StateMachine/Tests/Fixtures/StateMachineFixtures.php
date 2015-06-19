<?php


namespace StateMachine\Tests\Fixtures;

use StateMachine\Accessor\StateAccessor;
use StateMachine\State\StateInterface;
use StateMachine\StateMachine\StateMachine;
use StateMachine\Tests\Entity\Order;

class StateMachineFixtures
{
    public static function getOrderStateMachine()
    {
        $class = "StateMachine\Tests\Entity\Order";
        $object = new Order();

        $accessor = new StateAccessor('state');

        $stateMachine = new StateMachine($class, $object, $accessor);

        $stateMachine->addState('pending', StateInterface::TYPE_INITIAL);
        $stateMachine->addState('checking_out');
        $stateMachine->addState('purchased');
        $stateMachine->addState('shipped');
        $stateMachine->addState('cancelled');
        $stateMachine->addState('failed');
        $stateMachine->addState('refunded', StateInterface::TYPE_FINAL);

        $stateMachine->addTransition('pending', 'checking_out');
        $stateMachine->addTransition('pending', 'cancelled');
        $stateMachine->addTransition('checking_out', 'cancelled');
        $stateMachine->addTransition('checking_out', 'purchased');
        $stateMachine->addTransition('purchased', 'failed');
        $stateMachine->addTransition('purchased', 'shipped');
        $stateMachine->addTransition('shipped', 'refunded');

        return $stateMachine;
    }
}
