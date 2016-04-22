<?php

namespace StateMachineBundle\Tests\History;

use StateMachine\History\History;
use StateMachine\History\HistoryCollection;
use StateMachine\Tests\Entity\Order;
use StateMachineBundle\History\PersistentHistoryManager;
use StateMachineBundle\Tests\Entity\BlameableHistory;

class PersistentHistoryManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testHistoryAddWithFlushTest()
    {
        $objectManagerMock = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerMock->expects($this->once())
            ->method('persist');

        $objectManagerMock->expects($this->once())
            ->method('flush');

        $stateChangeMock = $this->getStateChangeEventMock(['transaction' => true]);

        $stateMachineMock = $this->getMockBuilder('StateMachine\StateMachine\StateMachineInterface')
            ->disableOriginalConstructor()
            ->getMock();

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

        $stateChangeMock = $this->getStateChangeEventMock(['transaction' => false]);

        $stateMachineMock = $this->getMockBuilder('StateMachine\StateMachine\StateMachineInterface')
            ->disableOriginalConstructor()
            ->getMock();

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
        $objectManagerMock = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerMock->expects($this->once())
            ->method('persist');

        $objectManagerMock->expects($this->once())
            ->method('flush');

        $blameableStateChange = new BlameableHistory();
        $blameableStateChange->setOptions(['transaction' => true]);

        $userMock = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');
        $tokenStorageMock = $this->getTokenStorageMock($userMock);

        $historyManager = $this->getHistoryManager(
            $this->getRegistryMock($objectManagerMock),
            $tokenStorageMock
        );

        $stateMachineMock = $this->getMockBuilder('StateMachine\StateMachine\StateMachineInterface')
            ->disableOriginalConstructor()
            ->getMock();

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

        $stateMachineMock = $this->getMockBuilder('StateMachine\StateMachine\StateMachineInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $stateMachineMock->expects($this->once())
            ->method('getHistory')->willReturn(new HistoryCollection());

        $objectMock->expects($this->once())
            ->method('getStateMachine')
            ->willReturn($stateMachineMock);

        $historyManager = $this->getHistoryManager($this->getRegistryMock($objectManagerMock), $tokenStorageMock);
        $historyManager->add($objectMock, $blameableStateChange);
        $this->assertNull($blameableStateChange->getUser());
    }

    public function testLoadIdSortingOrder()
    {
        $stateChange1 = new History();
        $stateChange1->setFromState('A');
        $stateChange1->setToState('B');

        $stateChange2 = new History();
        $stateChange2->setFromState('B');
        $stateChange2->setToState('C');

        $stateChange3 = new History();
        $stateChange3->setFromState('C');
        $stateChange3->setToState('D');

        $history = [$stateChange1, $stateChange2, $stateChange3];

        $objectManagerMock = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->getMock();

        $entityRepositoryMock = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $entityRepositoryMock->expects($this->exactly(1))
            ->method('findBy')
            ->with([
                'objectIdentifier' => 1,
            ],
                [
                    'createdAt' => 'asc',
                    'id' => 'asc',
                ])
            ->willReturn($history);

        $objectManagerMock->expects($this->exactly(1))
            ->method('getRepository')
            ->willReturn($entityRepositoryMock);

        $tokenStorageMock = $this->getTokenStorageMock();

        $object = new Order(1);

        $stateMachineMock = $this->getMockBuilder('StateMachine\StateMachine\StateMachineInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $stateMachineMock->expects($this->once())
            ->method('getHistoryClass');

        $object->setStateMachine($stateMachineMock);

        $historyManager = $this->getHistoryManager($this->getRegistryMock($objectManagerMock), $tokenStorageMock);
        $historyCollection = $historyManager->load($object, $stateMachineMock);

        $this->assertEquals(3, $historyCollection->count());
        $this->assertEquals('A', $historyCollection->first()->getFromState());
        $this->assertEquals('B', $historyCollection->first()->getToState());
    }

    public function testNotCheckingHistoryWithNewObject()
    {
        $objectManagerMock = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->getMock();

        $objectManagerMock->expects($this->exactly(0))
            ->method('getRepository');

        $tokenStorageMock = $this->getTokenStorageMock();

        $object = new Order(null);

        $stateMachineMock = $this->getMockBuilder('StateMachine\StateMachine\StateMachineInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $object->setStateMachine($stateMachineMock);

        $historyManager = $this->getHistoryManager($this->getRegistryMock($objectManagerMock), $tokenStorageMock);
        $historyCollection = $historyManager->load($object, $stateMachineMock);

        $this->assertEquals(0, $historyCollection->count());
    }

    public function testLoadCreatedAtSortingOrder()
    {
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
        $stateMachineMock = $this->getMockBuilder('StateMachine\StateMachine\StateMachineInterface')
            ->disableOriginalConstructor()
            ->getMock();

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
