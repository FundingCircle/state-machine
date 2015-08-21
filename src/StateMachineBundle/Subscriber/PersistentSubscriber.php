<?php

namespace StateMachineBundle\Subscriber;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use StateMachine\Event\Events;
use StateMachine\Event\TransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 */
class PersistentSubscriber implements EventSubscriberInterface
{
    /** @var ObjectManager */
    private $objectManager;

    /**
     * @param ObjectManager|EntityManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param TransitionEvent $transitionEvent
     *
     * @return bool
     */
    public function onPreTransition(TransitionEvent $transitionEvent)
    {
        $options = $transitionEvent->getOptions();
        if ($options['transaction'] == true
            && $this->objectManager instanceof EntityManager
        ) {
            $this->objectManager->beginTransaction();
        }

        return true;
    }

    /**
     * @param TransitionEvent $transitionEvent
     *
     * @return bool
     */
    public function onPostTransition(TransitionEvent $transitionEvent)
    {
        $object = $transitionEvent->getObject();
        $options = $transitionEvent->getOptions();
        $this->objectManager->persist($object);
        $this->objectManager->flush($object);

        if ($options['transaction'] == true
            && $this->objectManager instanceof EntityManager
        ) {
            $this->objectManager->commit();
        }

        return true;
    }

    /**
     * @param TransitionEvent $transitionEvent
     */
    public function onFailTransition(TransitionEvent $transitionEvent)
    {
        $options = $transitionEvent->getOptions();
        if ($options['transaction'] == true
            && $this->objectManager instanceof EntityManager
        ) {
            $this->objectManager->rollback();
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::EVENT_PRE_TRANSITION => 'onPreTransition',
            Events::EVENT_POST_TRANSITION => 'onPostTransition',
            Events::EVENT_FAIL_TRANSITION => 'onFailTransition',
        ];
    }
}
