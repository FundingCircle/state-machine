<?php

namespace StateMachine\StateMachine;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use StateMachine\Event\TransitionEvent;

class PersistentManager
{
    /** @var  ObjectManager|EntityManager */
    private $objectManager;

    /**
     * PersistentManager constructor.
     *
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param TransitionEvent $transitionEvent
     */
    public function beginTransaction(TransitionEvent $transitionEvent)
    {
        if (null !== $this->objectManager) {
            $options = $transitionEvent->getOptions();
            if ($this->objectManager instanceof EntityManager && $options['transaction'] == true
            ) {
                $this->objectManager->beginTransaction();
            }
        }
    }

    /**
     * @param TransitionEvent $transitionEvent
     */
    public function commitTransaction(TransitionEvent $transitionEvent)
    {
        if (null !== $this->objectManager) {
            $object = $transitionEvent->getObject();
            $options = $transitionEvent->getOptions();
            $this->objectManager->persist($object);
            $this->objectManager->flush($object);

            if ($this->objectManager instanceof EntityManager && $options['transaction'] == true
            ) {
                $this->objectManager->commit();
            }
        }
    }

    /**
     * @param TransitionEvent $transitionEvent
     */
    public function rollBackTransaction(TransitionEvent $transitionEvent)
    {
        if (null !== $this->objectManager) {
            $options = $transitionEvent->getOptions();
            if ($this->objectManager instanceof EntityManager && $options['transaction'] == true
            ) {
                $this->objectManager->rollback();
            }
        }
    }

    /**
     * @return ObjectManager|EntityManager
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }
}
