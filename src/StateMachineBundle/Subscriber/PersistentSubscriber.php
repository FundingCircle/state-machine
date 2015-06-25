<?php

namespace StateMachineBundle\Subscriber;

use Doctrine\Common\Persistence\ObjectManager;
use StateMachine\Event\Events;
use StateMachine\Event\TransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PersistentSubscriber implements EventSubscriberInterface
{
    /** @var ObjectManager */
    private $objectManager;

    /**
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param TransitionEvent $transitionEvent
     */
    public function onPostTransaction(TransitionEvent $transitionEvent)
    {
        $object = $transitionEvent->getObject();
        $options = $transitionEvent->getOptions();
        $this->objectManager->persist($object);

        if (isset($options['flush']) && $options['flush'] == true) {
            $this->objectManager->flush($object);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::EVENT_POST_TRANSITION => ['onPostTransaction', 255]
        ];
    }
}
