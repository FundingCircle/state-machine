<?php

namespace StateMachineBundle\Tests\Subscriber;

use Doctrine\Common\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use StateMachine\EventDispatcher\EventDispatcher;
use StateMachine\StateMachine\StateMachineInterface;
use StateMachineBundle\Subscriber\LifeCycleEventsSubscriber;
use StateMachineBundle\Tests\Entity\NonStatefulOrder;
use StateMachineBundle\Tests\Entity\Order;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LifeCycleEventsSubscriberTest extends TestCase
{
    public function testPostLoadWithNonStatefulEntity()
    {
        $this->markTestIncomplete();

        $smManagerMock = $this->getMockBuilder('\StateMachineBundle\StateMachine\StateMachineManager')
            ->disableOriginalConstructor()->getMock();

        $eventArgsMock = $this->getMockBuilder('\Doctrine\ORM\Event\LifecycleEventArgs')
            ->disableOriginalConstructor()->getMock();

        $eventArgsMock->expects($this->any())
            ->method('getEntity')
            ->willReturn(new NonStatefulOrder());

        $subscriber = new LifeCycleEventsSubscriber($smManagerMock, $this->getTokenStorage());

        $subscriber->postLoad($eventArgsMock);
    }

    public function testPostLoadWithStatefulEntity()
    {
        $stateMachineMock = $this->getMockBuilder(StateMachineInterface::class)
            ->setMethods(['getEventDispatcher'])
            ->getMockForAbstractClass();

        $stateMachineMock->expects($this->any())
            ->method('getEventDispatcher')
            ->willReturn($this->createMock(EventDispatcher::class));

        $smManagerMock = $this->getMockBuilder('\StateMachineBundle\StateMachine\StateMachineManager')
            ->disableOriginalConstructor()->getMock();

        $smManagerMock->expects($this->once())
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
            ->willReturn($this->createMock(ObjectManager::class));

        $subscriber = new LifeCycleEventsSubscriber($smManagerMock, $this->getTokenStorage());

        $subscriber->postLoad($eventArgsMock);
        $this->assertInstanceOf('StateMachine\StateMachine\StateMachineInterface', $stateFulObject->getStateMachine());
    }

    public function testPrePersistWithNonStatefulEntity()
    {
        $this->markTestIncomplete();

        $smManagerMock = $this->getMockBuilder('\StateMachineBundle\StateMachine\StateMachineManager')
            ->disableOriginalConstructor()->getMock();

        $eventArgsMock = $this->getMockBuilder('\Doctrine\ORM\Event\LifecycleEventArgs')
            ->disableOriginalConstructor()->getMock();

        $eventArgsMock->expects($this->any())
            ->method('getEntity')
            ->willReturn(new NonStatefulOrder());

        $eventArgsMock->expects($this->any())
            ->method('getEntityManager')
            ->willReturn($this->createMock(ObjectManager::class));

        $subscriber = new LifeCycleEventsSubscriber($smManagerMock, $this->getTokenStorage());

        $subscriber->prePersist($eventArgsMock);
    }

    public function testPrePersistWithStatefulEntity()
    {
        $stateMachineMock = $this->getMockBuilder(StateMachineInterface::class)
            ->setMethods(['getEventDispatcher'])
            ->getMockForAbstractClass();

        $stateMachineMock->expects($this->any())
            ->method('getEventDispatcher')
            ->willReturn($this->createMock(EventDispatcher::class));

        $smManagerMock = $this->getMockBuilder('\StateMachineBundle\StateMachine\StateMachineManager')
            ->disableOriginalConstructor()->getMock();

        $smManagerMock->expects($this->once())
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
            ->willReturn($this->createMock(ObjectManager::class));

        $subscriber = new LifeCycleEventsSubscriber($smManagerMock, $this->getTokenStorage());

        $subscriber->prePersist($eventArgsMock);
        $this->assertInstanceOf('StateMachine\StateMachine\StateMachineInterface', $stateFulObject->getStateMachine());
    }

    public function testGetSubscribedEvents()
    {
        $smManagerMock = $this->getMockBuilder('\StateMachineBundle\StateMachine\StateMachineManager')
            ->disableOriginalConstructor()->getMock();
        $subscriber = new LifeCycleEventsSubscriber($smManagerMock, $this->getTokenStorage());
        $this->assertEquals(['postLoad', 'prePersist', 'onClear'], $subscriber->getSubscribedEvents());
    }

    public function testOnClear()
    {
        $smManagerMock = $this->getMockBuilder('\StateMachineBundle\StateMachine\StateMachineManager')
            ->disableOriginalConstructor()->getMock();
        $smManagerMock->expects($this->once())
            ->method('clear');

        $subscriber = new LifeCycleEventsSubscriber($smManagerMock, $this->getTokenStorage());
        $subscriber->onClear();
    }

    private function getTokenStorage()
    {
        return $this->createMock(TokenStorageInterface::class);
    }
}
