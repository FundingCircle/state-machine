<?php

namespace StateMachineBundle\Tests\Listeners;

use StateMachineBundle\Listener\HistoryListener;
use StateMachineBundle\Tests\Entity\BlameableTransition;

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

        $transitionEventMock = $this->getTransitionEventMock(['flush' => true]);

        $listener = $this->getListener($objectManagerMock, $this->getTokenStorageMock());
        $listener->onHistoryChange($transitionEventMock);

    }

    public function testHistoryChangeWithoutFlush()
    {
        $objectManagerMock = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->getMock();

        $objectManagerMock->expects($this->once())
            ->method('persist');

        $objectManagerMock->expects($this->never())
            ->method('flush');

        $transitionEventMock = $this->getTransitionEventMock(['flush' => false]);

        $listener = $this->getListener($objectManagerMock, $this->getTokenStorageMock());
        $listener->onHistoryChange($transitionEventMock);
    }

    public function testHistoryChangeWithBlameableTransition()
    {
        $objectManagerMock = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->getMock();

        $objectManagerMock->expects($this->once())
            ->method('persist');

        $objectManagerMock->expects($this->once())
            ->method('flush');

        $blameableTransition = new BlameableTransition();

        $transitionEventMock = $this->getTransitionEventMock(['flush' => true], $blameableTransition);

        $userMock = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');
        $tokenStorageMock = $this->getTokenStorageMock($userMock);

        $listener = $this->getListener($objectManagerMock, $tokenStorageMock);
        $transitionEvent = $listener->onHistoryChange($transitionEventMock);
        $this->assertInstanceOf(
            'Symfony\Component\Security\Core\User\UserInterface',
            $transitionEvent->getTransition()->getUser()
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

        $blameableTransition = new BlameableTransition();

        $transitionEventMock = $this->getTransitionEventMock(['flush' => true], $blameableTransition);

        $tokenStorageMock = $this->getTokenStorageMock();

        $listener = $this->getListener($objectManagerMock, $tokenStorageMock);
        $listener->onHistoryChange($transitionEventMock);
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
            ['getHistory', 'getLastTransition']
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

        $transitionEventMock = $this->getMockBuilder('StateMachine\Event\TransitionEvent')
            ->disableOriginalConstructor()
            ->setMethods(['getTransition', 'getOptions'])
            ->getMock();

        $transitionEventMock->expects($this->any())
            ->method('getTransition')
            ->willReturn($transitionMock);

        $transitionEventMock->expects($this->once())
            ->method('getOptions')
            ->willReturn($options);

        return $transitionEventMock;
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
