<?php
namespace StateMachineBundle\Listener;

use Doctrine\Common\Persistence\ObjectManager;
use StateMachine\Event\Events;
use StateMachine\Event\TransitionEvent;
use StateMachine\Exception\StateMachineException;
use StateMachine\Listener\HistoryListener as BaseHistoryListener;
use StateMachineBundle\Model\TransitionBlameableInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class HistoryListener extends BaseHistoryListener implements EventSubscriberInterface
{
    /** @var ObjectManager */
    private $objectManager;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * @param ObjectManager         $objectManager
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(ObjectManager $objectManager, TokenStorageInterface $tokenStorage)
    {
        $this->objectManager = $objectManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function onHistoryChange(TransitionEvent $transitionEvent)
    {
        $transition = parent::onHistoryChange($transitionEvent);
        $options = $transitionEvent->getOptions();

        if ($transition instanceof TransitionBlameableInterface) {
            $user = $this->tokenStorage->getToken()->getUser();
            if (!$user instanceof UserInterface) {
                throw new StateMachineException(
                    "Unable to write statemachine history, because there's no logged in user"
                );
            }
            $transition->setUser($user);
        }

        $this->objectManager->persist($transition);
        if (isset($options['flush']) && $options['flush'] == true) {
            $this->objectManager->flush($transition);
        }

        return $transitionEvent;
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
