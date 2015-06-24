<?php


namespace StateMachine\Tests\Fixtures;

use StateMachine\Accessor\StateAccessor;
use StateMachine\State\StateInterface;
use StateMachine\StateMachine\StateMachine;
use StateMachine\Tests\Entity\Bid;
use StateMachine\Tests\Entity\Order;
use Symfony\Component\EventDispatcher\EventDispatcher;

class StateMachineFixtures
{
    public static function getOrderStateMachine()
    {
        $stateMachine = new StateMachine(new Order(1), new EventDispatcher(), new StateAccessor());

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

    public static function getBidStateMachine()
    {
        $stateMachine = new StateMachine(new Bid(2), new EventDispatcher(), new StateAccessor());

        $stateMachine->addState('new', StateInterface::TYPE_INITIAL);
        $stateMachine->addState('cancelled');
        $stateMachine->addState('originating');
        $stateMachine->addState('committed');
        $stateMachine->addState('error');
        $stateMachine->addState('paid', StateInterface::TYPE_FINAL);

        $stateMachine->addTransition('new', 'committed');
        $stateMachine->addTransition('originating', 'error');
        $stateMachine->addTransition('originating', 'paid');
        $stateMachine->addTransition('error', 'committed');
        $stateMachine->addTransition('committed', 'originating');

        return $stateMachine;
    }
}
