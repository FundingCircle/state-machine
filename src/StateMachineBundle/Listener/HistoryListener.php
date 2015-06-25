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

    /** @var  ContainerInterface */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
//        $this->objectManager = $objectManager;
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

        $this->getObjectManager()->persist($transition);
        $this->getObjectManager()->flush($transition);
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

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    private function getObjectManager()
    {
        return $this->container->get('doctrine.orm.entity_manager');
    }
}
