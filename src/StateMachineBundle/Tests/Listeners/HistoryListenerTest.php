<?php

namespace StateMachineBundle\Tests\Listeners;

use StateMachineBundle\Listener\HistoryListener;
use StateMachineBundle\Tests\Entity\BlameableHistory;

class HistoryListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSubscribedEvents()
    {
        $objectManagerMock = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->getMock();

        $listener = $this->getListener($objectManagerMock, $this->getTokenStorageMock());
        $this->assertEquals(
            ['statemachine.events.history_change' => 'onHistoryChange'],
            $listener->getSubscribedEvents()
        );
    }

    public function testHistoryChangeWithFlush()
    {
        $objectManagerMock = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->getMock();

        $objectManagerMock->expects($this->once())
            ->method('persist');

        $objectManagerMock->expects($this->once())
            ->method('flush');

        $stateChangeEventMock = $this->getStateChangeEventMock(['flush' => true]);

        $listener = $this->getListener($objectManagerMock, $this->getTokenStorageMock());
        $listener->onHistoryChange($stateChangeEventMock);

    }

    public function testHistoryChangeWithoutFlush()
    {
        $objectManagerMock = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->getMock();

        $objectManagerMock->expects($this->once())
            ->method('persist');

        $objectManagerMock->expects($this->never())
            ->method('flush');

        $stateChangeEventMock = $this->getStateChangeEventMock(['flush' => false]);

        $listener = $this->getListener($objectManagerMock, $this->getTokenStorageMock());
        $listener->onHistoryChange($stateChangeEventMock);
    }

    public function testHistoryChangeWithBlameableTransition()
    {
        $objectManagerMock = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->getMock();

        $objectManagerMock->expects($this->once())
            ->method('persist');

        $objectManagerMock->expects($this->once())
            ->method('flush');

        $blameableTransition = new BlameableHistory();
        $blameableTransition->setOptions(['flush' => true]);

        $userMock = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');
        $tokenStorageMock = $this->getTokenStorageMock($userMock);

        $listener = $this->getListener($objectManagerMock, $tokenStorageMock);
        $stateChangeEvent = $listener->onHistoryChange($blameableTransition);
        $this->assertInstanceOf(
            'Symfony\Component\Security\Core\User\UserInterface',
            $stateChangeEvent->getUser()
        );
    }

    public function testHistoryChangeWIthBlameableTransitionWithNoUser()
    {
        $this->setExpectedException(
            "StateMachine\Exception\StateMachineException",
            "Unable to write statemachine history, because there's no logged in user"
        );
        $objectManagerMock = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->getMock();

        $blameableTransition = new BlameableHistory();

        $tokenStorageMock = $this->getTokenStorageMock();

        $listener = $this->getListener($objectManagerMock, $tokenStorageMock);
        $listener->onHistoryChange($blameableTransition);
    }

    private function getStateChangeEventMock($options)
    {
        $stateMachineMock = $this->getMockClass(
            'StateMachine\StateMachine\StateMachineHistoryInterface',
            ['getHistory', 'getLastStateChange']
        );

        $statefulMock = $this->getMock(
            'StateMachine\State\StatefulInterface',
            [
                'getStateMachine',
                'setStateMachine',
                'getId'
            ]
        );

        $statefulMock->expects($this->any())
            ->method('getStateMachine')
            ->willReturn($stateMachineMock);

        $stateChangeEventMock = $this->getMockBuilder('StateMachine\History\History')
            ->disableOriginalConstructor()
            ->setMethods(['getTransition', 'getOptions'])
            ->getMock();

        $stateChangeEventMock->expects($this->once())
            ->method('getOptions')
            ->willReturn($options);

        return $stateChangeEventMock;
    }

    private function getTokenStorageMock($user = null)
    {
        $tokenStoragetMock = $this->getMockBuilder(
            'Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface'
        )->getMock();

        $tokenMock = $this->getMockBuilder(
            'Symfony\Component\Security\Core\Authentication\Token\TokenInterface'
        )->getMock();
        $tokenMock->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

        $tokenStoragetMock->expects($this->any())
            ->method('getToken')
            ->willReturn($tokenMock);

        return $tokenStoragetMock;

    }

    private function getListener($objectManagerMock, $tokenStorageMock)
    {
        return new HistoryListener($objectManagerMock, $tokenStorageMock);
    }
}
