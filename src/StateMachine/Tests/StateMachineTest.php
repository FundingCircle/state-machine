<?php

namespace StateMachine\Tests;

use StateMachine\Accessor\StateAccessor;
use StateMachine\Event\PreTransitionEvent;
use StateMachine\Event\TransitionEvent;
use StateMachine\Exception\StateMachineException;
use StateMachine\History\History;
use StateMachine\State\StateInterface;
use StateMachine\StateMachine\StateMachine;
use StateMachine\Tests\Entity\Order;
use StateMachine\Tests\Fixtures\StateMachineFixtures;

class StateMachineTest extends \PHPUnit_Framework_TestCase
{
    public function testCorrectObject()
    {
        $stateMachine = new StateMachine(new Order(1));
        $this->assertNotNull($stateMachine->getObject());
    }

    public function testWithNoInitialState()
    {
        $this->expectException(StateMachineException::class);
        $this->expectExceptionMessage('No initial state is found');

        $stateMachine = new StateMachine(new Order(1));
        $stateMachine->addState('pending');
        $stateMachine->addState('checking_out');
        $stateMachine->boot();
    }

    public function testTransitionObject()
    {
        $stateMachine = StateMachineFixtures::getBidStateMachine();
        $stateMachine->boot();
        $this->assertEquals(1, count($stateMachine->getCurrentState()->getTransitionObjects()));
    }

    public function testWithHistoryStateConflict()
    {
        $this->expectException(StateMachineException::class);
        $object = new Order(2);
        $object->setState('new');
        $stateMachine = new StateMachine($object);

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

        $lastStateChange = new History();
        $lastStateChange->setFromState('new');
        $lastStateChange->setToState('committed');
        $stateMachine->getHistory()->add($lastStateChange);

        $stateMachine->boot();
    }

    public function testWithHistoryStateNoConflict()
    {
        $object = new Order(2);
        $object->setState('committed');
        $stateMachine = new StateMachine($object);

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

        $lastStateChange = new History();
        $lastStateChange->setFromState('new');
        $lastStateChange->setToState('committed');
        $stateMachine->getHistory()->add($lastStateChange);
        $stateMachine->boot();
        $this->assertEquals('committed', $stateMachine->getCurrentState());
    }

    public function testTwoInitialStates()
    {
        $this->expectException(StateMachineException::class);
        $this->expectExceptionMessage(
            'Statemachine cannot have more than one initial state, current initial state is (pending)'
        );

        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->addState('another_initial_state', StateInterface::TYPE_INITIAL);
    }

    public function testFromAnyTransition()
    {
        $stateMachine = StateMachineFixtures::getBidStateMachine();
        $stateMachine->addTransition(null, 'cancelled');
        $transitions = $stateMachine->getTransitions();
        $this->assertArrayHasKey('new->cancelled', $transitions);
        $this->assertArrayHasKey('error->cancelled', $transitions);
        $this->assertArrayHasKey('committed->cancelled', $transitions);
        $this->assertArrayHasKey('paid->cancelled', $transitions);
        $this->assertArrayHasKey('cancelled->cancelled', $transitions);
        $stateMachine->boot();
        $this->assertEquals(2, count($stateMachine->getCurrentState()->getTransitionObjects()));
    }

    public function testToAnyTransition()
    {
        $stateMachine = StateMachineFixtures::getBidStateMachine();
        $stateMachine->addTransition('paid', null);
        $transitions = $stateMachine->getTransitions();
        $this->assertArrayHasKey('paid->new', $transitions);
        $this->assertArrayHasKey('paid->cancelled', $transitions);
        $this->assertArrayHasKey('paid->originating', $transitions);
        $this->assertArrayHasKey('paid->committed', $transitions);
        $this->assertArrayHasKey('paid->error', $transitions);
        $this->assertArrayHasKey('paid->paid', $transitions);
    }

    public function testFromManyTransitions()
    {
        $stateMachine = StateMachineFixtures::getBidStateMachine();
        $stateMachine->addState('from_many');
        $stateMachine->addTransition(['paid', 'originating'], 'from_many');
        $transitions = $stateMachine->getTransitions();
        $this->assertArrayHasKey('originating->from_many', $transitions);
        $this->assertArrayHasKey('paid->from_many', $transitions);
    }

    public function testToManyTransitions()
    {
        $stateMachine = StateMachineFixtures::getBidStateMachine();
        $stateMachine->addTransition('paid', ['new', 'originating']);
        $transitions = $stateMachine->getTransitions();
        $this->assertArrayHasKey('paid->new', $transitions);
        $this->assertArrayHasKey('paid->originating', $transitions);
    }

    public function testFromManyToAll()
    {
        $stateMachine = StateMachineFixtures::getBidStateMachine();
        $stateMachine->addState('source1');
        $stateMachine->addState('source2');
        $stateMachine->addTransition(['source1', 'source2'], null);
        $transitions = $stateMachine->getTransitions();
        $this->assertArrayHasKey('source1->new', $transitions);
        $this->assertArrayHasKey('source2->new', $transitions);

        $this->assertArrayHasKey('source1->cancelled', $transitions);
        $this->assertArrayHasKey('source2->cancelled', $transitions);

        $this->assertArrayHasKey('source1->originating', $transitions);
        $this->assertArrayHasKey('source2->originating', $transitions);

        $this->assertArrayHasKey('source1->committed', $transitions);
        $this->assertArrayHasKey('source2->committed', $transitions);

        $this->assertArrayHasKey('source1->error', $transitions);
        $this->assertArrayHasKey('source2->error', $transitions);

        $this->assertArrayHasKey('source1->paid', $transitions);
        $this->assertArrayHasKey('source2->paid', $transitions);
    }

    public function testFromAllToMany()
    {
        $stateMachine = StateMachineFixtures::getBidStateMachine();
        $stateMachine->addState('destination1');
        $stateMachine->addState('destination2');
        $stateMachine->addTransition(null, ['destination1', 'destination2']);
        $transitions = $stateMachine->getTransitions();
        $this->assertArrayHasKey('new->destination1', $transitions);
        $this->assertArrayHasKey('new->destination2', $transitions);

        $this->assertArrayHasKey('cancelled->destination1', $transitions);
        $this->assertArrayHasKey('cancelled->destination2', $transitions);

        $this->assertArrayHasKey('originating->destination1', $transitions);
        $this->assertArrayHasKey('originating->destination2', $transitions);

        $this->assertArrayHasKey('committed->destination1', $transitions);
        $this->assertArrayHasKey('committed->destination2', $transitions);

        $this->assertArrayHasKey('error->destination1', $transitions);
        $this->assertArrayHasKey('error->destination2', $transitions);

        $this->assertArrayHasKey('paid->destination1', $transitions);
        $this->assertArrayHasKey('paid->destination2', $transitions);
    }

    public function testAllowedTransitions()
    {
        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->boot();

        $allowedTransitions = $stateMachine->getAllowedTransitions();

        $this->assertEquals('pending', $stateMachine->getCurrentState());

        $this->assertEquals(['checking_out', 'cancelled'], $allowedTransitions);
        $this->assertTrue($stateMachine->canTransitionTo('cancelled'));
        $this->assertFalse($stateMachine->canTransitionTo('shipped'));
    }

    public function testNotAllowedTransition()
    {
        $this->expectException(StateMachineException::class);
        $this->expectExceptionMessage(
            "There's no transition defined from (pending) to (shipped), allowed transitions to : [ checking_out,cancelled ]"
        );

        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->boot();
        $stateMachine->transitionTo('shipped');
    }

    public function testTransitionTo()
    {
        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->boot();
        $stateMachine->transitionTo('checking_out');
        $this->assertEquals($stateMachine->getCurrentState(), 'checking_out');
    }

    public function testWithWrongProperty()
    {
        $this->expectException(StateMachineException::class);
        $stateMachine = new StateMachine(new Order(1), null, null, new StateAccessor('wrong_state'));

        $stateMachine->addState('pending', StateInterface::TYPE_INITIAL);
        $stateMachine->addState('checking_out');

        $stateMachine->addTransition('pending', 'checking_out');
        $stateMachine->boot();
        $stateMachine->transitionTo('checking_out');
    }

    public function testStateTypes()
    {
        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->boot();
        $this->assertEquals($stateMachine->getCurrentState()->getType(), StateInterface::TYPE_INITIAL);
        $this->assertTrue($stateMachine->getCurrentState()->isInitial());

        $stateMachine->transitionTo('checking_out');
        $stateMachine->transitionTo('purchased');
        $stateMachine->transitionTo('shipped');
        $this->assertTrue($stateMachine->getCurrentState()->isNormal());
        $stateMachine->transitionTo('refunded');
        $this->assertEquals($stateMachine->getCurrentState()->getType(), StateInterface::TYPE_FINAL);
        $this->assertTrue($stateMachine->getCurrentState()->isFinal());
    }

    public function testAddTransitionToBootedMachine()
    {
        $this->expectException(StateMachineException::class);
        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->addTransition('new', 'cancelled');
        $stateMachine->boot();
    }

    public function testPreTransitionToBootedMachine()
    {
        $this->expectException(StateMachineException::class);
        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->boot();
        $stateMachine->addPostTransition(
            'new->committed',
            function () {
            }
        );
    }

    public function testPostTransitionToBootedMachine()
    {
        $this->expectException(StateMachineException::class);
        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->boot();
        $stateMachine->addPreTransition(
            'new->committed',
            function () {
            }
        );
    }

    public function testGuardToBootedMachine()
    {
        $this->expectException(StateMachineException::class);
        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->boot();
        $stateMachine->addGuard(
            'new->committed',
            function () {
            }
        );
    }

    public function testAddTransitionWithBootedMachine()
    {
        $this->expectException(StateMachineException::class);
        $this->expectExceptionMessage('Cannot add more transitions to booted StateMachine');
        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->boot();
        $stateMachine->addTransition('new->committed');
    }

    public function testGetAllowedTransitionsForNonBootedMachine()
    {
        $this->expectException(StateMachineException::class);
        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->getAllowedTransitions();
    }

    public function testCanTransitToForNonBootedMachine()
    {
        $this->expectException(StateMachineException::class);
        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->canTransitionTo('paid');
    }

    public function testTransitToForNonBootedMachine()
    {
        $this->expectException(StateMachineException::class);
        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->transitionTo('paid');
    }

    public function testBootTwice()
    {
        $this->expectException(StateMachineException::class);
        $this->expectExceptionMessage('Statemachine is already booted');
        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->boot();
        $stateMachine->boot();
    }

    public function testInitStateCallback()
    {
        $object = new Order(null);
        $stateMachine = new StateMachine(
            $object
        );
        $stateMachine->addState('pending', StateInterface::TYPE_INITIAL);

        $stateMachine->setInitCallback(
            function (TransitionEvent $event) {
                $event->getObject()->setSomeValue('some value');
            }
        );
        $stateMachine->boot();
        $this->assertEquals('some value', $object->getSomeValue());
    }

    public function testInitStateCallbackNotCalled()
    {
        $object = new Order(1);
        $stateMachine = new StateMachine(
            $object
        );
        $stateMachine->addState('pending', StateInterface::TYPE_INITIAL);

        $stateMachine->setInitCallback(
            function (TransitionEvent $event) {
                $event->getObject()->setSomeValue('some value');
            }
        );
        $stateMachine->boot();
        $this->assertNull($object->getSomeValue());
    }

    public function testInitStateEmptyCallback()
    {
        $object = new Order(1);
        $stateMachine = new StateMachine(
            $object
        );
        $stateMachine->addState('pending', StateInterface::TYPE_INITIAL);

        $stateMachine->setInitCallback(
            function (TransitionEvent $event) {
                //do nothing
            }
        );
        $stateMachine->boot();
        $this->assertNull($object->getSomeValue());
    }

    public function testMessagesThroughMultiStates()
    {
        $object = new Order(1);
        $stateMachine = new StateMachine($object);
        $stateMachine->addState('state-a', StateInterface::TYPE_INITIAL);
        $stateMachine->addState('state-b');
        $stateMachine->addState('state-c');
        $stateMachine->addTransition('state-a', 'state-b', 'to-b');
        $stateMachine->addTransition('state-a', 'state-c', 'to-c');
        $stateMachine->addPreTransition(
            function (PreTransitionEvent $event) {
                $event->addMessage('message-1');
                $event->setTargetState('state-c');
            },
            'state-a',
            'state-b'
        );
        $stateMachine->addPreTransition(
            function (TransitionEvent $event) {
                $event->addMessage('message-2');
            },
            'state-a',
            'state-c'
        );

        $stateMachine->boot();
        $stateMachine->triggers('to-b');
        $messages = $stateMachine->getMessages();

        $this->assertEquals('state-c', $stateMachine->getCurrentState()->getName());
        $this->assertInternalType('array', $messages);
        $this->assertCount(2, $messages);
        $this->assertEquals('message-1', $messages[0]);
        $this->assertEquals('message-2', $messages[1]);
    }

    public function testMessagesThroughMultiStatesInit()
    {
        $object = new Order(null);
        $stateMachine = new StateMachine($object);
        $stateMachine->addState('A', StateInterface::TYPE_INITIAL);
        $stateMachine->addState('B');
        $stateMachine->addState('C');
        $stateMachine->addTransition('A', 'C');

        $stateMachine->setInitCallback(
            function (TransitionEvent $event) {
                $event->addMessage('on init message');
                $event->getObject()->getStateMachine()->transitionTo('C');
            }
        );
        $stateMachine->addPreTransition(
            function (TransitionEvent $event) {
                $event->addMessage('normal message');
            },
            'A',
            'C'
        );

        $stateMachine->boot();

        $messages = $stateMachine->getMessages();

        $this->assertEquals('C', $stateMachine->getCurrentState()->getName());
        $this->assertInternalType('array', $messages);
        $this->assertCount(2, $messages);
        $this->assertEquals('on init message', $messages[1]);
        $this->assertEquals('normal message', $messages[0]);
    }

    public function testNewObjectWithStateValueSetToNormal()
    {
        $this->expectException(StateMachineException::class);
        $this->expectExceptionMessage('Object has state: B, which is not final or initial');
        //new object
        $object = new Order(null);
        $object->setState('B');

        $stateMachine = new StateMachine($object);

        $stateMachine->addState('A', StateInterface::TYPE_INITIAL);
        $stateMachine->addState('B');
        $stateMachine->addState('C', StateInterface::TYPE_FINAL);

        $stateMachine->boot();
    }

    public function testNewObjectWithStateValueSetToFinal()
    {
        //new object
        $object = new Order(null);
        $object->setState('A');

        $stateMachine = new StateMachine($object);

        $stateMachine->addState('A', StateInterface::TYPE_INITIAL);
        $stateMachine->addState('B');
        $stateMachine->addState('C', StateInterface::TYPE_FINAL);

        $stateMachine->boot();

        $this->assertEquals('A', $stateMachine->getCurrentState());
    }

    public function testNewObjectWithStateValueSetToInitial()
    {
        //new object
        $object = new Order(null);
        $object->setState('C');

        $stateMachine = new StateMachine($object);

        $stateMachine->addState('A', StateInterface::TYPE_INITIAL);
        $stateMachine->addState('B');
        $stateMachine->addState('C', StateInterface::TYPE_FINAL);

        $stateMachine->boot();

        $this->assertEquals('C', $stateMachine->getCurrentState());
    }

    public function testNotAllowedEvent()
    {
        $this->expectException(StateMachineException::class);
        $this->expectExceptionMessage('Event A_D didn\'t match any transition, allowed events for state A are [A_B,A_C]');
        //new object
        $object = new Order(1);
        $object->setState('A');

        $stateMachine = new StateMachine($object);

        $stateMachine->addState('A', StateInterface::TYPE_INITIAL);
        $stateMachine->addState('B');
        $stateMachine->addState('C', StateInterface::TYPE_FINAL);

        $stateMachine->addTransition('A', 'B', 'A_B');
        $stateMachine->addTransition('A', 'C', 'A_C');
        $stateMachine->addTransition('B', 'C', 'B_C');

        $stateMachine->boot();

        $stateMachine->triggers('A_D');
    }
}
