<?php

namespace StateMachine\Tests;

use Doctrine\Common\Persistence\ObjectManager;
use StateMachine\StateMachine\PersistentManager;
use StateMachine\StateMachine\StatefulInterface;

class PersistentManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testPreTransitionWithORM()
    {
        $objectManagerMock = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerMock->expects($this->once())
            ->method('beginTransaction');

        $subscriber = new PersistentManager($objectManagerMock);
        $transitionEventMock = $this->getTransitionEventMock(['transaction' => true]);
        $subscriber->beginTransaction($transitionEventMock);
    }

    public function testPreTransitionWithoutORM()
    {
        $objectManagerMock = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['beginTransaction'])
            ->getMockForAbstractClass();

        $objectManagerMock->expects($this->never())
            ->method('beginTransaction');

        $subscriber = new PersistentManager($objectManagerMock);
        $transitionEventMock = $this->getTransitionEventMock(['transaction' => true]);
        $subscriber->beginTransaction($transitionEventMock);
    }

    public function testFailTransitionWithoutORM()
    {
        $objectManagerMock = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['rollBack'])
            ->getMockForAbstractClass();

        $objectManagerMock->expects($this->never())
            ->method('rollBack');

        $subscriber = new PersistentManager($objectManagerMock);
        $transitionEventMock = $this->getTransitionEventMock(['transaction' => true]);
        $subscriber->rollBackTransaction($transitionEventMock);
    }

    public function testFailTransitionWithORM()
    {
        $objectManagerMock = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerMock->expects($this->once())
            ->method('rollBack');

        $subscriber = new PersistentManager($objectManagerMock);
        $transitionEventMock = $this->getTransitionEventMock(['transaction' => true]);
        $subscriber->rollBackTransaction($transitionEventMock);
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
            ->method('commit');

        $subscriber = new PersistentManager($objectManagerMock);
        $transitionEventMock = $this->getTransitionEventMock(['transaction' => true]);
        $subscriber->commitTransaction($transitionEventMock);
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
            ->method('commit');

        $subscriber = new PersistentManager($objectManagerMock);
        $transitionEventMock = $this->getTransitionEventMock(['transaction' => false]);
        $subscriber->commitTransaction($transitionEventMock);
    }

    private function getTransitionEventMock($options, $transitionMock = null)
    {
        if (null == $transitionMock) {
            $transitionMock = $this->getMockBuilder('StateMachine\Transition\TransitionInterface')
                ->disableOriginalConstructor()
                ->getMock();
        }

        $stateMachineMock = $this->getMockBuilder('StateMachine\StateMachine\StateMachineInterface')
            ->disableOriginalConstructor()->getMock();

        $statefulMock = $this->getMockBuilder(StatefulInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStateMachine', 'setStateMachine', 'getId'])
            ->getMock();

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
