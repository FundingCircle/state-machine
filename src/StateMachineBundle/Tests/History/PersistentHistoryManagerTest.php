<?php

namespace StateMachineBundle\Tests\History;

use StateMachine\History\HistoryCollection;
use StateMachineBundle\History\PersistentHistoryManager;
use StateMachineBundle\Tests\Entity\BlameableHistory;

class PersistentHistoryManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testHistoryAddWithFlushTest()
    {
        $objectManagerMock = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->getMock();

        $objectManagerMock->expects($this->once())
            ->method('persist');

        $objectManagerMock->expects($this->once())
            ->method('flush');

        $stateChangeMock = $this->getStateChangeEventMock(['flush' => true]);

        $stateMachineMock = $this->getMock(
            'StateMachine\StateMachine\StateMachineHistoryInterface',
            ['getHistory', 'getLastStateChange', 'getHistoryClass', 'getObject']
        );

        $stateMachineMock->expects($this->once())
            ->method('getHistory')->willReturn(new HistoryCollection());

        $objectMock = $this->getMockBuilder("StateMachineBundle\Tests\Entity\Order")
            ->disableOriginalConstructor()
            ->getMock();

        $objectMock->expects($this->once())
            ->method('getStateMachine')
            ->willReturn($stateMachineMock);

        $historyManager = $this->getHistoryManager(
            $this->getRegistryMock($objectManagerMock),
            $this->getTokenStorageMock()
        );
        $historyManager->add($objectMock, $stateChangeMock);
    }

    public function testHistoryAddWithoutFlushTest()
    {
        $objectManagerMock = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->getMock();

        $objectManagerMock->expects($this->once())
            ->method('persist');

        $objectManagerMock->expects($this->never())
            ->method('flush');

        $stateChangeMock = $this->getStateChangeEventMock(['flush' => false]);

        $stateMachineMock = $this->getMock(
            'StateMachine\StateMachine\StateMachineHistoryInterface',
            ['getHistory', 'getLastStateChange', 'getHistoryClass', 'getObject']
        );

        $stateMachineMock->expects($this->once())
            ->method('getHistory')->willReturn(new HistoryCollection());

        $objectMock = $this->getMockBuilder("StateMachineBundle\Tests\Entity\Order")
            ->disableOriginalConstructor()
            ->getMock();

        $objectMock->expects($this->once())
            ->method('getStateMachine')
            ->willReturn($stateMachineMock);

        $historyManager = $this->getHistoryManager(
            $this->getRegistryMock($objectManagerMock),
            $this->getTokenStorageMock()
        );
        $historyManager->add($objectMock, $stateChangeMock);
    }

    public function testHistoryChangeWithBlameableTransition()
    {
        $objectManagerMock = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->getMock();

        $objectManagerMock->expects($this->once())
            ->method('persist');

        $objectManagerMock->expects($this->once())
            ->method('flush');

        $blameableStateChange = new BlameableHistory();
        $blameableStateChange->setOptions(['flush' => true]);

        $userMock = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');
        $tokenStorageMock = $this->getTokenStorageMock($userMock);

        $historyManager = $this->getHistoryManager(
            $this->getRegistryMock($objectManagerMock),
            $tokenStorageMock
        );

        $stateMachineMock = $this->getMock(
            'StateMachine\StateMachine\StateMachineHistoryInterface',
            ['getHistory', 'getLastStateChange', 'getHistoryClass', 'getObject']
        );

        $stateMachineMock->expects($this->once())
            ->method('getHistory')->willReturn(new HistoryCollection());

        $objectMock = $this->getMockBuilder("StateMachineBundle\Tests\Entity\Order")
            ->disableOriginalConstructor()
            ->getMock();

        $objectMock->expects($this->once())
            ->method('getStateMachine')
            ->willReturn($stateMachineMock);

        $stateChange = $historyManager->add($objectMock, $blameableStateChange);

        $this->assertInstanceOf(
            'Symfony\Component\Security\Core\User\UserInterface',
            $stateChange->getUser()
        );
    }

    public function testHistoryChangeWIthBlameableTransitionWithNoUser()
    {
        $objectManagerMock = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->getMock();

        $blameableStateChange = new BlameableHistory();

        $tokenStorageMock = $this->getTokenStorageMock();

        $objectMock = $this->getMockBuilder("StateMachineBundle\Tests\Entity\Order")
            ->disableOriginalConstructor()
            ->getMock();

        $stateMachineMock = $this->getMock(
            'StateMachine\StateMachine\StateMachineHistoryInterface',
            ['getHistory', 'getLastStateChange', 'getHistoryClass', 'getObject']
        );

        $stateMachineMock->expects($this->once())
            ->method('getHistory')->willReturn(new HistoryCollection());

        $objectMock->expects($this->once())
            ->method('getStateMachine')
            ->willReturn($stateMachineMock);

        $historyManager = $this->getHistoryManager($this->getRegistryMock($objectManagerMock), $tokenStorageMock);
        $historyManager->add($objectMock, $blameableStateChange);
        $this->assertNull($blameableStateChange->getUser());
    }

    private function getRegistryMock($objectManagerMock)
    {
        $registryMock = $this->getMockBuilder("Symfony\Bridge\Doctrine\RegistryInterface")
            ->getMock();

        $registryMock->expects($this->any())->method('getManagerForClass')
            ->willReturn($objectManagerMock);

        return $registryMock;
    }

    private function getStateChangeEventMock($options)
    {
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

    private function getHistoryManager($registryMock, $tokenStorageMock)
    {
        return new PersistentHistoryManager($registryMock, $tokenStorageMock);
    }
}
