<?php

namespace StateMachineBundle\Tests\Subscriber;

use StateMachineBundle\Subscriber\PersistentSubscriber;

class PersistentSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSubscribedEvents()
    {
        $this->assertEquals(
            [
                'statemachine.events.pre_transition'  => 'onPreTransition',
                'statemachine.events.post_transition' => 'onPostTransition',
                'statemachine.events.fail_transition' => 'onFailTransition',
            ],
            PersistentSubscriber::getSubscribedEvents()
        );
    }

    public function testPreTransitionWithORM()
    {
        $objectManagerMock = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerMock->expects($this->once())
            ->method("beginTransaction");

        $subscriber = new PersistentSubscriber($objectManagerMock);
        $transitionEventMock = $this->getTransitionEventMock(['transaction' => true]);
        $subscriber->onPreTransition($transitionEventMock);
    }

    public function testPreTransitionWithoutORM()
    {
        $objectManagerMock = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerMock->expects($this->never())
            ->method("beginTransaction");

        $subscriber = new PersistentSubscriber($objectManagerMock);
        $transitionEventMock = $this->getTransitionEventMock(['transaction' => true]);
        $subscriber->onPreTransition($transitionEventMock);
    }

    public function testFailTransitionWithoutORM()
    {
        $objectManagerMock = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerMock->expects($this->never())
            ->method("rollBack");

        $subscriber = new PersistentSubscriber($objectManagerMock);
        $transitionEventMock = $this->getTransitionEventMock(['transaction' => true]);
        $subscriber->onFailTransition($transitionEventMock);
    }

    public function testFailTransitionWithORM()
    {
        $objectManagerMock = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerMock->expects($this->once())
            ->method("rollBack");

        $subscriber = new PersistentSubscriber($objectManagerMock);
        $transitionEventMock = $this->getTransitionEventMock(['transaction' => true]);
        $subscriber->onFailTransition($transitionEventMock);
    }

    public function testPostTransitionWithTransaction()
    {
        $objectManagerMock = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerMock->expects($this->once())
            ->method('persist');

        $objectManagerMock->expects($this->once())
            ->method('flush');

        $objectManagerMock->expects($this->once())
            ->method("commit");

        $subscriber = new PersistentSubscriber($objectManagerMock);
        $transitionEventMock = $this->getTransitionEventMock(['transaction' => true]);
        $subscriber->onPostTransition($transitionEventMock);
    }

    public function testPostTransitionWithWithoutTransaction()
    {
        $objectManagerMock = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerMock->expects($this->once())
            ->method('persist');

        $objectManagerMock->expects($this->once())
            ->method('flush');

        $objectManagerMock->expects($this->never())
            ->method("commit");

        $subscriber = new PersistentSubscriber($objectManagerMock);
        $transitionEventMock = $this->getTransitionEventMock(['transaction' => false]);
        $subscriber->onPostTransition($transitionEventMock);
    }

    private function getTransitionEventMock($options, $transitionMock = null)
    {
        if ($transitionMock == null) {
            $transitionMock = $this->getMockBuilder('StateMachine\Transition\TransitionInterface')
                ->disableOriginalConstructor()
                ->getMock();
        }

        $stateMachineMock = $this->getMockClass(
            'StateMachine\StateMachine\StateMachineHistoryInterface',
            ['getHistory', 'getLastStateChange', 'getHistoryClass', 'getObject']
        );

        $statefulMock = $this->getMock(
            'StateMachine\StateMachine\StatefulInterface',
            [
                'getStateMachine',
                'setStateMachine',
                'getId',
            ]
        );

        $statefulMock->expects($this->any())
            ->method('getStateMachine')
            ->willReturn($stateMachineMock);

        $transitionEventMock = $this->getMockBuilder('StateMachine\Event\TransitionEvent')
            ->disableOriginalConstructor()
            ->setMethods(['getObject', 'getTransition', 'getOptions'])
            ->getMock();

        $transitionEventMock->expects($this->any())
            ->method('getObject')
            ->willReturn($statefulMock);

        $transitionEventMock->expects($this->any())
            ->method('getTransition')
            ->willReturn($transitionMock);

        $transitionEventMock->expects($this->once())
            ->method('getOptions')
            ->willReturn($options);

        return $transitionEventMock;
    }
}
