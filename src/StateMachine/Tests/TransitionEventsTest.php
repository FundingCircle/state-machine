<?php
namespace StateMachine\Tests;

use StateMachine\Tests\Fixtures\StateMachineFixtures;

class TransitionEventsTest extends \PHPUnit_Framework_TestCase
{
    public function testPreTransitionOnNonExistingTransition()
    {
        $stateMachine = StateMachineFixtures::getBidStateMachine();
    }

    public function testPreTransitionWithStopPropagation()
    {

    }

    public function testPreTransitionSuccess()
    {

    }
}
