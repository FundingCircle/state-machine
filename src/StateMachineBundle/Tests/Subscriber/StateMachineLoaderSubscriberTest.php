<?php

namespace StateMachineBundle\Tests\Subscriber;

use StateMachineBundle\Subscriber\StateMachineLoaderSubscriber;
use StateMachineBundle\Tests\Entity\NonStatefulOrder;
use StateMachineBundle\Tests\Entity\Order;

class StateMachineLoaderSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function testPostLoadWithNonStateFulEntity()
    {
        $factoryMock = $this->getMockBuilder('\StateMachineBundle\StateMachine\StateMachineManager')
            ->disableOriginalConstructor()->getMock();

        $eventArgsMock = $this->getMockBuilder('\Doctrine\ORM\Event\LifecycleEventArgs')
            ->disableOriginalConstructor()->getMock();

        $eventArgsMock->expects($this->any())
            ->method('getEntity')
            ->willReturn(new NonStatefulOrder());

        $subscriber = new StateMachineLoaderSubscriber($factoryMock, $this->getTokenStorage());

        $subscriber->postLoad($eventArgsMock);
    }

    public function testPostLoadWithStateFulEntity()
    {
        $stateMachineMock = $this->getMock('StateMachine\StateMachine\StateMachineInterface');
        $stateMachineMock->expects($this->any())
            ->method('getEventDispatcher')
            ->willReturn($this->getMock("StateMachine\EventDispatcher\EventDispatcher"));

        $factoryMock = $this->getMockBuilder('\StateMachineBundle\StateMachine\StateMachineManager')
            ->disableOriginalConstructor()->getMock();

        $factoryMock->expects($this->once())
            ->method('get')
            ->willReturn($stateMachineMock);

        $eventArgsMock = $this->getMockBuilder('\Doctrine\ORM\Event\LifecycleEventArgs')
            ->disableOriginalConstructor()->getMock();

        $stateFulObject = new Order(2);
        $eventArgsMock->expects($this->any())
            ->method('getEntity')
            ->willReturn($stateFulObject);

        $eventArgsMock->expects($this->any())
            ->method('getEntityManager')
            ->willReturn($this->getMock('Doctrine\Common\Persistence\ObjectManager'));

        $subscriber = new StateMachineLoaderSubscriber($factoryMock, $this->getTokenStorage());

        $subscriber->postLoad($eventArgsMock);
        $this->assertInstanceOf('StateMachine\StateMachine\StateMachineInterface', $stateFulObject->getStateMachine());
    }

    public function testPrePersistWithNonStateFulEntity()
    {
        $factoryMock = $this->getMockBuilder('\StateMachineBundle\StateMachine\StateMachineManager')
            ->disableOriginalConstructor()->getMock();

        $eventArgsMock = $this->getMockBuilder('\Doctrine\ORM\Event\LifecycleEventArgs')
            ->disableOriginalConstructor()->getMock();

        $eventArgsMock->expects($this->any())
            ->method('getEntity')
            ->willReturn(new NonStatefulOrder());

        $eventArgsMock->expects($this->any())
            ->method('getEntityManager')
            ->willReturn($this->getMock('Doctrine\Common\Persistence\ObjectManager'));

        $subscriber = new StateMachineLoaderSubscriber($factoryMock, $this->getTokenStorage());

        $subscriber->prePersist($eventArgsMock);
    }

    public function testPrePersistWithStateFulEntity()
    {
        $stateMachineMock = $this->getMock('StateMachine\StateMachine\StateMachineInterface');
        $stateMachineMock->expects($this->any())
            ->method('getEventDispatcher')
            ->willReturn($this->getMock("StateMachine\EventDispatcher\EventDispatcher"));

        $factoryMock = $this->getMockBuilder('\StateMachineBundle\StateMachine\StateMachineManager')
            ->disableOriginalConstructor()->getMock();

        $factoryMock->expects($this->once())
            ->method('get')
            ->willReturn($stateMachineMock);

        $eventArgsMock = $this->getMockBuilder('\Doctrine\ORM\Event\LifecycleEventArgs')
            ->disableOriginalConstructor()->getMock();

        $stateFulObject = new Order(2);
        $eventArgsMock->expects($this->any())
            ->method('getEntity')
            ->willReturn($stateFulObject);

        $eventArgsMock->expects($this->any())
            ->method('getEntityManager')
            ->willReturn($this->getMock('Doctrine\Common\Persistence\ObjectManager'));

        $subscriber = new StateMachineLoaderSubscriber($factoryMock, $this->getTokenStorage());

        $subscriber->prePersist($eventArgsMock);
        $this->assertInstanceOf('StateMachine\StateMachine\StateMachineInterface', $stateFulObject->getStateMachine());
    }

    public function testGetSubscribedEvents()
    {
        $factoryMock = $this->getMockBuilder('\StateMachineBundle\StateMachine\StateMachineManager')
            ->disableOriginalConstructor()->getMock();
        $subscriber = new StateMachineLoaderSubscriber($factoryMock, $this->getTokenStorage());
        $this->assertEquals(['postLoad', 'prePersist'], $subscriber->getSubscribedEvents());
    }

    private function getTokenStorage()
    {
        $tokenStorageMock = $this->getMock(
            'Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface'
        );

        return $tokenStorageMock;
    }
}
