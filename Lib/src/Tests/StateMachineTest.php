<?php
namespace StateMachine\Tests;

use StateMachine\Accessor\StateAccessor;
use StateMachine\State\State;
use StateMachine\StateMachine\StateMachine;
use StateMachine\Tests\Entity\Order;

class StateMachineTest extends \PHPUnit_Framework_TestCase
{
    public function testOrderBasicMachine()
    {
        $class = "StateMachine\Tests\Entity\Order";
        $test = new State('new', []);
        $object = new Order();
        $accessor = new StateAccessor('state');
        $stateMachine = new StateMachine($class, $accessor, $object);
    }
}
