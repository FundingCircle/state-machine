<?php
namespace StateMachineBundle\Listener;

use Doctrine\Common\Persistence\ObjectManager;
use StateMachine\Event\Events;
use StateMachine\Event\TransitionEvent;
use StateMachine\Listener\HistoryListener as BaseHistoryListener;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HistoryListener extends BaseHistoryListener implements EventSubscriberInterface
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
     * {@inheritdoc}
     */
    public function onHistoryChange(
        TransitionEvent $transitionEvent,
        $eventName,
        EventDispatcherInterface $eventDispatcher
    ) {
        $transition = parent::onHistoryChange(
            $transitionEvent,
            $eventName,
            $eventDispatcher
        );
        $options = $transitionEvent->getOptions();

        $this->objectManager->persist($transition);
        if (isset($options['flush']) && $options['flush'] == true) {
            $this->objectManager->flush($transition);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::EVENT_HISTORY_CHANGE => 'onHistoryChange'
        ];
    }
}
