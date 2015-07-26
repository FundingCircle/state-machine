<?php
namespace StateMachineBundle\Listener;

use Doctrine\Common\Persistence\ObjectManager;
use StateMachine\Event\BootEvent;
use StateMachine\Event\Events;
use StateMachine\Event\TransitionEvent;
use StateMachine\Exception\StateMachineException;
use StateMachine\History\History;
use StateMachine\Listener\HistoryListener as BaseHistoryListener;
use StateMachine\StateMachine\StateMachineHistoryInterface;
use StateMachineBundle\Model\BlameableStateChange;
use StateMachineBundle\Model\BlameableStateChangeInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class HistoryListener implements EventSubscriberInterface
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
    public function onHistoryChange(History $stateChangeEvent)
    {
        $options = $stateChangeEvent->getOptions();

        if ($stateChangeEvent instanceof BlameableStateChangeInterface) {
            $user = $this->tokenStorage->getToken()->getUser();
            if (!$user instanceof UserInterface) {
                throw new StateMachineException(
                    "Unable to write statemachine history, because there's no logged in user"
                );
            }
            $stateChangeEvent->setUser($user);
        }

        $this->objectManager->persist($stateChangeEvent);
        if (isset($options['flush']) && $options['flush'] == true) {
            $this->objectManager->flush($stateChangeEvent);
        }

        return $stateChangeEvent;
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
