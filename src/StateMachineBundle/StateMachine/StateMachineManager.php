<?php

namespace StateMachineBundle\StateMachine;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\Proxy;
use StateMachine\Accessor\StateAccessor;
use StateMachine\Exception\StateMachineException;
use StateMachine\History\HistoryManagerInterface;
use StateMachine\StateMachine\ManagerInterface;
use StateMachine\StateMachine\PersistentManager;
use StateMachine\StateMachine\StatefulInterface;
use StateMachine\StateMachine\StateMachine;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This factory is responsible of registering statemachine definition
 * and create statemachines on demand,
 * This is the only place where statemachine is created and booted.
 */
class StateMachineManager implements ContainerAwareInterface, ManagerInterface
{
    /** @var  HistoryManagerInterface */
    private $historyManager;

    /** @var  ContainerInterface */
    private $container;

    /** @var  array */
    private $stateMachineDefinitions;

    /** @var  array */
    private $stateFullClasses;

    /** @var array */
    private $loadedObjects;

    /** @var  Registry */
    private $doctrine;

    /**
     * @param HistoryManagerInterface $historyManager
     * @param Registry                $doctrine
     */
    public function __construct(HistoryManagerInterface $historyManager, Registry $doctrine)
    {
        $this->historyManager = $historyManager;
        $this->doctrine = $doctrine;
        $this->stateFullClasses = [];
        $this->loadedObjects = [];
    }

    /**
     * Container is injected here for purpose to avoid circular references
     * and instead loading services in runtime requires container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     *
     * @return self
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Register statemachine definition.
     *
     * @param array $definition
     */
    public function register(array $definition)
    {
        $this->stateMachineDefinitions[$definition['object']['class']] = $definition;
        $this->stateFullClasses[] = $definition['object']['class'];
    }

    /**
     * Get all state machines defintions.
     *
     * @return array
     */
    public function getDefinitions()
    {
        return $this->stateMachineDefinitions;
    }

    /**
     * Get one definition by id.
     *
     * @param $id
     *
     * @return array
     *
     * @throws StateMachineException
     */
    public function getDefinition($id)
    {
        foreach ($this->stateMachineDefinitions as $definition) {
            if ($definition['id'] == $id) {
                return $definition;
            }
        }

        throw new StateMachineException(sprintf("can't find definition %s", $id));
    }

    /**
     * Create and boot statemachine for given stateful object.
     *
     * @param StatefulInterface $statefulObject
     *
     * @return StateMachine
     *
     * @throws StateMachineException
     */
    public function get(StatefulInterface $statefulObject)
    {
        $oid = spl_object_hash($statefulObject);
        //to avoid pre-persist twice on same object
        if (array_key_exists($oid, $this->loadedObjects)) {
            return $this->loadedObjects[$oid];
        }

        $class = $this->getClass($statefulObject);
        if (!isset($this->stateMachineDefinitions[$class])) {
            throw new StateMachineException(
                sprintf(
                    'Definition for class :%s is not found, have you forgot to define statemachine in config.yml',
                    get_class($statefulObject)
                )
            );
        }
        // get definition prepared by the container
        $definition = $this->stateMachineDefinitions[$class];
        //defining the StateMachine
        $stateMachine = new StateMachine(
            $statefulObject,
            new PersistentManager($this->getObjectManagerByObject($statefulObject)),
            $this->historyManager,
            new StateAccessor($definition['object']['property']),
            $definition['options'],
            $definition['history_class'],
            $definition['id']
        );

        //adding states
        foreach ($definition['states'] as $name => $state) {
            $stateMachine->addState($name, $state['type']);
        }

        //adding transitions
        foreach ($definition['transitions'] as $transition) {
            $from = empty($transition['from']) ? null : $transition['from'];
            $to = empty($transition['to']) ? null : $transition['to'];
            $event = empty($transition['event']) ? null : $transition['event'];
            $stateMachine->addTransition($from, $to, $event);
        }

        //adding init callback
        if (isset($definition['on_init'])) {
            $initCallBack = $definition['on_init'];
            $stateMachine->setInitCallback(
                [$this->resolveCallback($initCallBack), $initCallBack['method']]
            );
        }

        //adding guards
        foreach ($definition['guards'] as $guard) {
            if (!isset($guard['callback'])) {
                $guard['callback'] = $class;
            }
            $stateMachine->addGuard(
                [$this->resolveCallback($guard), $guard['method']],
                $guard['from'],
                $guard['to']
            );
        }
        //adding pre-transitions
        foreach ($definition['pre_transitions'] as $preTransition) {
            if (!isset($preTransition['callback'])) {
                $preTransition['callback'] = $class;
            }
            $stateMachine->addPreTransition(
                [$this->resolveCallback($preTransition), $preTransition['method']],
                $preTransition['from'],
                $preTransition['to']
            );
        }
        //adding post-transitions
        foreach ($definition['post_transitions'] as $postTransition) {
            if (!isset($postTransition['callback'])) {
                $postTransition['callback'] = $class;
            }
            $stateMachine->addPostTransition(
                [$this->resolveCallback($postTransition), $postTransition['method']],
                $postTransition['from'],
                $postTransition['to']
            );
        }
        $stateMachine->setManager($this);

        //add to loaded StateMachine objects
        $this->loadedObjects[$oid] = $stateMachine;

        return $stateMachine;
    }

    /**
     * {@inheritdoc}
     */
    public function add(StatefulInterface $object)
    {
        $stateMachine = $this->get($object);
        $stateMachine->boot();

        return $stateMachine;
    }

    /**
     * @param $object
     *
     * @return ObjectManager
     */
    private function getObjectManagerByObject($object)
    {
        return $this->doctrine->getManagerForClass($this->getClass($object));
    }

    /**
     * Get the class of an Doctrine entity.
     *
     * @param StatefulInterface $statefulObject
     *
     * @return string
     */
    private function getClass(StatefulInterface $statefulObject)
    {
        //if proxy class get the original class
        $class = ($statefulObject instanceof Proxy)
            ? get_parent_class($statefulObject)
            : get_class($statefulObject);

        //if class is found in the registered list
        if (isset($this->stateMachineDefinitions[$class])) {
            return $class;
        } else { //in case of a child class
            foreach ($this->stateFullClasses as $stateFullClass) {
                if (is_subclass_of($class, $stateFullClass)) {
                    return $stateFullClass;
                }
            }
        }
    }

    /**
     * Detect if it's class or service.
     *
     * @param $callback
     *
     * @return Reference
     */
    private function resolveCallback($callback)
    {
        if (class_exists($callback['callback'])) {
            return $callback['callback'];
        } else {
            //@TODO improve that, get rid of container
            return $this->container->get($callback['callback']);
        }
    }
}
