<?php

namespace StateMachineBundle\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use StateMachine\StateMachine\StatefulInterface;
use StateMachineBundle\StateMachine\StateMachineManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class StateMachineLoaderSubscriber implements EventSubscriber
{
    /** @var StateMachineManager */
    private $stateMachineFactory;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var bool */
    public static $enabled = true;

    /**
     * @param StateMachineManager   $stateMachineFactory
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(StateMachineManager $stateMachineFactory, TokenStorageInterface $tokenStorage)
    {
        $this->stateMachineFactory = $stateMachineFactory;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'postLoad',
            'prePersist',
        ];
    }

    /**
     * Check if loaded entity is Stateful and attach the corresponded statemachine to it.
     *
     * @param LifecycleEventArgs $eventArgs
     *
     * @throws \StateMachine\Exception\StateMachineException
     */
    public function postLoad(LifecycleEventArgs $eventArgs)
    {
        if (!static::$enabled) {
            return;
        }
        $entity = $eventArgs->getEntity();

        if ($entity instanceof StatefulInterface) {
            $stateMachine = $this->stateMachineFactory->get($entity);
            $entity->setStateMachine($stateMachine);
        }
    }

    /**
     * While creating new object set statemachine and initial state.
     *
     * @param LifecycleEventArgs $eventArgs
     *
     * @throws \StateMachine\Exception\StateMachineException
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        if (!static::$enabled) {
            return;
        }
        $entity = $eventArgs->getEntity();

        if ($entity instanceof StatefulInterface && $entity->getStateMachine() == null) {
            $stateMachine = $this->stateMachineFactory->get($entity);
            $entity->setStateMachine($stateMachine);
            $stateMachine->boot();
        }
    }
}
