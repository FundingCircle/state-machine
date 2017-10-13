<?php

namespace StateMachineBundle\Tests;

use PHPUnit\Framework\TestCase;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use StateMachine\Exception\StateMachineException;
use StateMachine\History\HistoryCollection;
use StateMachineBundle\StateMachine\StateMachineManager;
use StateMachineBundle\Tests\Entity\ChildOrder;
use StateMachineBundle\Tests\Entity\Order;
use StateMachineBundle\Tests\Listeners\MockListener;
use Symfony\Component\DependencyInjection\ContainerInterface;

class StateMachineManagerTest extends TestCase
{
    public function testGetUndefinedStateMachine()
    {
        $this->expectException(StateMachineException::class);
        $factory = $this->getFactory();
        $factory->get(new Order(1));
    }

    public function testGetStateMachine()
    {
        $definition = $this->getDefinition();

        $factory = $this->getFactory();
        $factory->register($definition);
        $stateMachine = $factory->get(new Order(1));
        $stateMachine->boot();

        $this->assertEquals('new', $stateMachine->getCurrentState()->getName());
        $this->assertEquals(1, count($stateMachine->getTransitions()['new->cancelled']->getPostTransitions()));
        $this->assertEquals(1, count($stateMachine->getTransitions()['new->cancelled']->getPostCommits()));
        $this->assertEquals(1, count($stateMachine->getTransitions()['new->cancelled']->getPreTransitions()));
        $this->assertEquals('order_statemachine', $stateMachine->getName());
    }

    public function testClassWithStatefulParent()
    {
        $definition = [
            'object' => [
                'class' => "StateMachineBundle\Tests\Entity\Order",
                'property' => 'state',
            ],
            'version' => 1,
            'id' => 'order_statemachine',
            'history_class' => "StateMachineBundle\Tests\Entity\History",
            'options' => ['flush' => true],
            'states' => [
                'new' => [
                    'type' => 'initial',
                ],
                'cancelled' => [
                    'type' => 'normal',
                ],
                'originating' => [
                    'type' => 'normal',
                ],
                'committed' => [
                    'type' => 'normal',
                ],
                'error' => [
                    'type' => 'normal',
                ],
                'paid' => [
                    'type' => 'final',
                ],
            ],
            'transitions' => [],
            'guards' => [],
            'pre_transitions' => [],
            'post_transitions' => [],
            'post_commits' => [],
        ];

        $factory = $this->getFactory();
        $factory->register($definition);
        $stateMachine = $factory->get(new ChildOrder(1));
        $stateMachine->boot();
        $this->assertEquals('new', $stateMachine->getCurrentState()->getName());
    }

    public function testGetAllDefinitions()
    {
        $definition = $this->getDefinition();
        $version = $definition['version'];
        $factory = $this->getFactory();
        $factory->register($definition);
        $definitions = $factory->getDefinitions();
        $this->assertEquals($definition, reset($definitions)[$version]);
    }

    public function testRegisterDefinitionTwice()
    {
        $this->expectException(StateMachineException::class);
        $this->expectExceptionMessage(
            'Cannot register statemachine\'s same class, with same version for more than one time, class: StateMachineBundle\Tests\Entity\Order'
        );
        $definition = $this->getDefinition();
        $factory = $this->getFactory();
        $factory->register($definition);
        $factory->register($definition);
    }

    public function testGetOneDefinition()
    {
        $definition = $this->getDefinition();
        $version = $definition['version'];
        $factory = $this->getFactory();
        $factory->register($definition);
        $definition = $factory->getDefinition('order_statemachine', $version);
        $this->assertEquals('order_statemachine', $definition['id']);
    }

    public function testGetOneNotFoundDefinition()
    {
        $this->expectException(StateMachineException::class);
        $definition = $this->getDefinition();
        $factory = $this->getFactory();
        $factory->register($definition);
        $factory->getDefinition('not_found_statemachine');
    }

    public function testClearEmpty()
    {
        $definition = $this->getDefinition();

        $historyManagerMock = $this->getMockBuilder('StateMachine\History\HistoryManagerInterface')
            ->setMethods(['load', 'add'])
            ->getMock();

        $historyManagerMock->expects($this->any())
            ->method('load')
            ->willReturn(new HistoryCollection());

        $entityManagerMock = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $containerMock = $this->createMock(ContainerInterface::class);
        $containerMock->method('get')
            ->willReturn(new MockListener());

        $doctrineMock = $this->getMockBuilder('\Doctrine\Bundle\DoctrineBundle\Registry')
            ->disableOriginalConstructor()
            ->getMock();

        $doctrineMock->method('getManagerForClass')
            ->willReturn($entityManagerMock);

        $lazyLoadingFactory = $this->getMockBuilder('ProxyManager\Factory\LazyLoadingValueHolderFactory')
            ->getMock();
        $lazyLoadingFactory->expects($this->exactly(1))
            ->method('createProxy');

        $factory = new StateMachineManager($historyManagerMock, $doctrineMock, $lazyLoadingFactory);

        $factory->setContainer($containerMock);

        $factory->register($definition);
        $order = new Order(1);
        $factory->get($order);
        $factory->get($order);
    }

    public function testClearWithEntities()
    {
        $definition = $this->getDefinition();

        $historyManagerMock = $this->getMockBuilder('StateMachine\History\HistoryManagerInterface')
            ->setMethods(['load', 'add'])
            ->getMock();

        $historyManagerMock->expects($this->any())
            ->method('load')
            ->willReturn(new HistoryCollection());

        $entityManagerMock = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $containerMock = $this->createMock(ContainerInterface::class);
        $containerMock->method('get')
            ->willReturn(new MockListener());

        $doctrineMock = $this->getMockBuilder('\Doctrine\Bundle\DoctrineBundle\Registry')
            ->disableOriginalConstructor()
            ->getMock();

        $doctrineMock->method('getManagerForClass')
            ->willReturn($entityManagerMock);

        $lazyLoadingFactory = $this->getMockBuilder('ProxyManager\Factory\LazyLoadingValueHolderFactory')
            ->getMock();
        $lazyLoadingFactory->expects($this->exactly(2))
            ->method('createProxy');

        $factory = new StateMachineManager($historyManagerMock, $doctrineMock, $lazyLoadingFactory);

        $factory->setContainer($containerMock);

        $factory->register($definition);
        $factory->get(new Order(1));
        $factory->clear();
        $factory->get(new Order(1));
    }

    private function getFactory()
    {
        $historyManagerMock = $this->getMockBuilder('StateMachine\History\HistoryManagerInterface')
            ->setMethods(['load', 'add'])
            ->getMock();

        $historyManagerMock->expects($this->any())
            ->method('load')
            ->willReturn(new HistoryCollection());

        $entityManagerMock = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $containerMock = $this->createMock(ContainerInterface::class);
        $containerMock->method('get')
            ->willReturn(new MockListener());

        $doctrineMock = $this->getMockBuilder('\Doctrine\Bundle\DoctrineBundle\Registry')
            ->disableOriginalConstructor()
            ->getMock();

        $doctrineMock->method('getManagerForClass')
            ->willReturn($entityManagerMock);

        $factory = new StateMachineManager($historyManagerMock, $doctrineMock, new LazyLoadingValueHolderFactory());

        return $factory->setContainer($containerMock);
    }

    private function getDefinition()
    {
        return [
            'version' => 1,
            'id' => 'order_statemachine',
            'object' => [
                'class' => "StateMachineBundle\Tests\Entity\Order",
                'property' => 'state',
            ],
            'history_class' => "StateMachineBundle\Tests\Entity\History",
            'options' => ['flush' => true],
            'states' => [
                'new' => [
                    'type' => 'initial',
                ],
                'cancelled' => [
                    'type' => 'normal',
                ],
                'originating' => [
                    'type' => 'normal',
                ],
                'committed' => [
                    'type' => 'normal',
                ],
                'error' => [
                    'type' => 'normal',
                ],
                'paid' => [
                    'type' => 'final',
                ],
            ],
            'transitions' => [
                't1' => [
                    'from' => [],
                    'to' => [
                        0 => 'cancelled',
                    ],
                ],
            ],
            'guards' => [
                0 => [
                    'callback' => 'test_callback',
                    'method' => 'callbackMethod',
                    'from' => 'new',
                    'to' => 'cancelled',
                ],
            ],
            'pre_transitions' => [
                0 => [
                    'callback' => 'test_callback',
                    'method' => 'callbackMethod_pre',
                    'from' => 'new',
                    'to' => 'cancelled',
                ],
            ],
            'post_transitions' => [
                0 => [
                    'callback' => 'test_callback',
                    'method' => 'callbackMethod_post',
                    'from' => ['new', 'originating'],
                    'to' => 'cancelled',
                ],
            ],
            'post_commits' => [
                0 => [
                    'callback' => 'test_callback',
                    'method' => 'callbackMethod_post_commit',
                    'from' => ['new', 'originating'],
                    'to' => 'cancelled',
                ],
            ],
        ];
    }
}
