<?php

namespace StateMachineBundle\Tests\Subscriber;

use StateMachineBundle\Subscriber\PersistentSubscriber;

class PersistentSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSubscribedEvents()
    {
        $objectManagerMock = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->getMock();
        $subscriber = new PersistentSubscriber($objectManagerMock);
        $this->assertEquals(
            ['statemachine.events.post_transition' => ['onPostTransaction', 255]],
            $subscriber->getSubscribedEvents()
        );
    }

    public function testPostTransitionWithFlush()
    {
        $objectManagerMock = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->getMock();
        $objectManagerMock->expects($this->once())
            ->method('persist');

        $objectManagerMock->expects($this->once())
            ->method('flush');

        $subscriber = new PersistentSubscriber($objectManagerMock);
        $transitionEventMock = $this->getTransitionEventMock(['flush' => true]);
        $subscriber->onPostTransaction($transitionEventMock);
    }

    public function testPostTransitionWithNoFlush()
    {
        $objectManagerMock = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->getMock();
        $objectManagerMock->expects($this->once())
            ->method('persist');

        $objectManagerMock->expects($this->never())
            ->method('flush');

        $subscriber = new PersistentSubscriber($objectManagerMock);
        $transitionEventMock = $this->getTransitionEventMock(['flush' => false]);
        $subscriber->onPostTransaction($transitionEventMock);
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

        $transitionEventMock->expects($this->once())
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
