<?php

namespace StateMachine\Tests;

use StateMachine\Exception\StateMachineException;
use StateMachine\Tests\Fixtures\StateMachineFixtures;

class EventsTest extends \PHPUnit_Framework_TestCase
{
    public function testTriggersUndefinedEvent()
    {
        $this->expectException(StateMachineException::class);
        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->boot();

        $stateMachine->triggers('armageddon');
    }

    public function testTriggersSuccess()
    {
        $stateMachine = StateMachineFixtures::getBidStateMachine();
        $stateMachine->boot();

        $return = $stateMachine->triggers('commit');
        $this->assertTrue($return);
    }

    public function testGetAllowedEvents()
    {
        $stateMachine = StateMachineFixtures::getBidStateMachine();
        $stateMachine->addTransition(null, 'cancelled', 'cancel');
        $stateMachine->addTransition('cancelled', null, 'reopen');
        $stateMachine->addTransition(null, 'error', 'fail');
        $stateMachine->boot();

        $allowedEvents = $stateMachine->getAllowedEvents();
        $this->assertArrayHasKey('new->committed', $allowedEvents);
        $this->assertArrayHasKey('new->cancelled', $allowedEvents);
        $this->assertArrayHasKey('new->error', $allowedEvents);

        $this->assertEquals($allowedEvents['new->committed'], 'commit');
        $this->assertEquals($allowedEvents['new->cancelled'], 'cancel');
        $this->assertEquals($allowedEvents['new->error'], 'fail');

        $return = $stateMachine->triggers('cancel');

        $this->assertTrue($return);

        $allowedEvents = $stateMachine->getAllowedEvents();
        $this->assertArrayHasKey('cancelled->new', $allowedEvents);
        $this->assertArrayHasKey('cancelled->originating', $allowedEvents);
        $this->assertArrayHasKey('cancelled->committed', $allowedEvents);
        $this->assertArrayHasKey('cancelled->error', $allowedEvents);
        $this->assertArrayHasKey('cancelled->paid', $allowedEvents);

        $this->assertEquals($allowedEvents['cancelled->new'], 'reopen');
        $this->assertEquals($allowedEvents['cancelled->originating'], 'reopen');
        $this->assertEquals($allowedEvents['cancelled->committed'], 'reopen');
        $this->assertEquals($allowedEvents['cancelled->paid'], 'reopen');
    }
}
