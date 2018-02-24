<?php

namespace StateMachine\Tests;

use PHPUnit\Framework\TestCase;
use StateMachine\Event\TransitionEvent;
use StateMachine\State\StateInterface;
use StateMachine\StateMachine\StateMachine;
use StateMachine\Tests\Fixtures\StateMachineFixtures;
use StateMachineBundle\Tests\Entity\Order;

class GuardsTest extends TestCase
{
    public function testGuardExistingTransitionWithTrueReturn()
    {
        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->addGuard(
            function () {
                return true;
            },
            'pending',
            'checking_out'
        );
        $stateMachine->boot();
        $return = $stateMachine->transitionTo('checking_out');
        $this->assertTrue($return);
        $this->assertEmpty($stateMachine->getLastStateChange()->getMessages());
    }

    public function testGuardExistingTransitionWithFalseReturn()
    {
        $stateMachine = StateMachineFixtures::getOrderStateMachine();
        $stateMachine->addGuard(
            function (TransitionEvent $transitionEvent) {
                $transitionEvent->addMessage('Transition is rejected by guard');

                return false;
            },
            'pending',
            'checking_out'
        );
        $stateMachine->boot();
        $return = $stateMachine->transitionTo('checking_out');

        $this->assertFalse($return);
        $this->assertArraySubset(
            ['Transition is rejected by guard'],
            $stateMachine->getMessages()
        );
    }

    public function testMultiStateMachineWithOneGuard()
    {
        $stateMachine1 = StateMachineFixtures::getOrderStateMachine();
        $stateMachine2 = StateMachineFixtures::getOrderStateMachine();

        $stateMachine1->addGuard(
            function (TransitionEvent $transitionEvent) {
                $transitionEvent->addMessage('Transition is rejected by guard');

                return false;
            },
            'pending',
            'checking_out'
        );
        $stateMachine1->boot();
        $stateMachine2->boot();

        $this->assertTrue($stateMachine2->canTransitionTo('checking_out'));
        $this->assertTrue($stateMachine2->transitionTo('checking_out'));

        $this->assertTrue($stateMachine1->canTransitionTo('checking_out'));
        $this->assertFalse($stateMachine1->canTransitionTo('checking_out', true));
        $this->assertFalse($stateMachine1->transitionTo('checking_out'));
    }

    public function testGuardFailedGuardRollBackTransaction()
    {
        $persistentManagerMock = $this->getMockBuilder('StateMachine\StateMachine\PersistentManager')
            ->disableOriginalConstructor()
            ->getMock();

        $persistentManagerMock->expects($this->exactly(1))
            ->method('beginTransaction');

        $persistentManagerMock->expects($this->exactly(1))
            ->method('commitTransaction');

        $persistentManagerMock->expects($this->exactly(0))
            ->method('rollBackTransaction');

        $persistentManagerMock->expects($this->exactly(0))
            ->method('save');

        $stateMachine = new StateMachine(
            new Order(1),
            $persistentManagerMock
        );

        $stateMachine->addState('A', StateInterface::TYPE_INITIAL);
        $stateMachine->addState('B');
        $stateMachine->addState('C', StateInterface::TYPE_FINAL);

        $stateMachine->addTransition('A', 'B');
        $stateMachine->addTransition('B', 'C');

        $stateMachine->addGuard(
            function (TransitionEvent $transitionEvent) {
                $transitionEvent->addMessage('Transition is rejected by guard');

                return false;
            },
            'A',
            'B'
        );

        $stateMachine->boot();
        $stateMachine->transitionTo('B');
    }

    public function testGuardNoGuardRollBackTransaction()
    {
        $persistentManagerMock = $this->getMockBuilder('StateMachine\StateMachine\PersistentManager')
            ->disableOriginalConstructor()
            ->getMock();

        $persistentManagerMock->expects($this->exactly(1))
            ->method('beginTransaction');

        $persistentManagerMock->expects($this->exactly(1))
            ->method('commitTransaction');

        $persistentManagerMock->expects($this->exactly(1))
            ->method('save');

        $persistentManagerMock->expects($this->exactly(0))
            ->method('rollBackTransaction');

        $stateMachine = new StateMachine(
            new Order(1),
            $persistentManagerMock
        );

        $stateMachine->addState('A', StateInterface::TYPE_INITIAL);
        $stateMachine->addState('B');
        $stateMachine->addState('C', StateInterface::TYPE_FINAL);

        $stateMachine->addTransition('A', 'B');
        $stateMachine->addTransition('B', 'C');

        $stateMachine->boot();
        $stateMachine->transitionTo('B');
    }
}
