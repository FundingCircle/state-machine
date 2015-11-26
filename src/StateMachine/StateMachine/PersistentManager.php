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
}
