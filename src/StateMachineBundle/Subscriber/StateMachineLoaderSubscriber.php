<?php

namespace StateMachineBundle\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use StateMachine\State\StatefulInterface;
use StateMachine\StateMachine\StateMachineHistoryInterface;
use StateMachine\StateMachine\StateMachineInterface;
use StateMachineBundle\Listener\HistoryListener;
use StateMachineBundle\StateMachine\StateMachineFactory;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class StateMachineLoaderSubscriber implements EventSubscriber
{
    /** @var StateMachineFactory */
    private $stateMachineFactory;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * @param StateMachineFactory $stateMachineFactory
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(StateMachineFactory $stateMachineFactory, TokenStorageInterface $tokenStorage)
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
            $stateMachine->getEventDispatcher()->addSubscriber(
                new PersistentSubscriber($eventArgs->getEntityManager())
            );
            $stateMachine->getEventDispatcher()->addSubscriber(
                new HistoryListener($eventArgs->getEntityManager(), $this->tokenStorage)
            );
            $this->loadHistory($stateMachine, $eventArgs->getEntityManager());

            $stateMachine->boot();
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
            $stateMachine->getEventDispatcher()->addSubscriber(
                new PersistentSubscriber($eventArgs->getEntityManager())
            );
            $stateMachine->getEventDispatcher()->addSubscriber(
                new HistoryListener($eventArgs->getEntityManager(), $this->tokenStorage)
            );
            $stateMachine->boot();
            $entity->setStateMachine($stateMachine);
        }
    }

    /**
     * @param StateMachineInterface $stateMachine
     * @param ObjectManager         $em
     */
    private function loadHistory(StateMachineInterface $stateMachine, ObjectManager $em)
    {
        if ($stateMachine instanceof StateMachineHistoryInterface) {
            $stateChanges = $em->getRepository($stateMachine->getHistoryClass())->findBy(
                [
                    'objectIdentifier' => $stateMachine->getObject()->getId()
                ],
                [
                    'createdAt' => 'desc'
                ]
            );

            foreach ($stateChanges as $stateChange) {
                $stateMachine->getHistory()->add($stateChange);
            }
        }
    }
}
