<?php

namespace StateMachineBundle\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use StateMachine\State\StatefulInterface;
use StateMachineBundle\StateMachine\StateMachineFactory;

class StateMachineLoaderSubscriber implements EventSubscriber
{
    /** @var StateMachineFactory */
    private $stateMachineFactory;

    /**
     * @param StateMachineFactory $stateMachineFactory
     */
    public function __construct(StateMachineFactory $stateMachineFactory)
    {
        $this->stateMachineFactory = $stateMachineFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'postLoad',
            'prePersist'
        ];
    }

    /**
     * Check if loaded entity is StateFul and attach the corresponded statemachine to it
     *
     * @param LifecycleEventArgs $eventArgs
     *
     * @throws \StateMachine\Exception\StateMachineException
     */
    public function postLoad(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();

        if ($entity instanceof StatefulInterface) {
            $stateMachine = $this->stateMachineFactory->get($entity);
            $entity->setStateMachine($stateMachine);
        }
    }

    /**
     * While creating new object set statemachine and initial state
     *
     * @param LifecycleEventArgs $eventArgs
     *
     * @throws \StateMachine\Exception\StateMachineException
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();

        if ($entity instanceof StatefulInterface && $entity->getStateMachine() == null) {
            $stateMachine = $this->stateMachineFactory->get($entity);
            $entity->setStateMachine($stateMachine);
        }
    }
}
