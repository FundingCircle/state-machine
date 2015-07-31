<?php

namespace StateMachineBundle\Subscriber;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use StateMachine\Event\Events;
use StateMachine\Event\TransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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
     */
    public function onPreTransaction(TransitionEvent $transitionEvent)
    {
        $options = $transitionEvent->getOptions();
        if ($options['transaction'] == true
            && $this->objectManager instanceof EntityManager
        ) {
            $this->objectManager->beginTransaction();
        }
    }

    /**
     * @param TransitionEvent $transitionEvent
     */
    public function onPostTransaction(TransitionEvent $transitionEvent)
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
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::EVENT_POST_TRANSITION => ['onPostTransaction'],
            Events::EVENT_PRE_TRANSITION  => ['onPreTransaction'],
        ];
    }
}
