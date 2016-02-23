<?php

namespace StateMachineBundle\History;

use Doctrine\ORM\EntityManager;
use StateMachine\History\History;
use StateMachine\History\HistoryCollection;
use StateMachine\History\HistoryManagerInterface;
use StateMachine\StateMachine\StatefulInterface;
use StateMachine\StateMachine\StateMachineInterface;
use StateMachineBundle\Model\BlameableStateChangeInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class PersistentHistoryManager implements HistoryManagerInterface
{
    /** @var RegistryInterface */
    private $registry;
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * @param RegistryInterface     $registry
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(RegistryInterface $registry, TokenStorageInterface $tokenStorage)
    {
        $this->registry = $registry;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function load(StatefulInterface $statefulObject, StateMachineInterface $stateMachine)
    {
        $om = $this->registry->getManagerForClass(get_class($statefulObject));
        $stateChanges = $om->getRepository($stateMachine->getHistoryClass())->findBy(
            [
                'objectIdentifier' => $statefulObject->getId(),
            ],
            [
                'createdAt' => 'asc',
                'id' => 'asc',
            ]
        );

        return new HistoryCollection($stateChanges);
    }

    /**
     * {@inheritdoc}
     */
    public function add(StatefulInterface $statefulObject, History $stateChange)
    {
        $options = $stateChange->getOptions();
        $om = $this->registry->getManagerForClass(get_class($statefulObject));

        if ($stateChange instanceof BlameableStateChangeInterface) {
            $user = $this->getUser();
            $stateChange->setUser($user);
        }

        $om->persist($stateChange);
        if (isset($options['transaction']) && $options['transaction'] == true) {
            if ($om instanceof EntityManager) {
                $om->flush($stateChange);
            }
        }

        $statefulObject->getStateMachine()->getHistory()->add($stateChange);

        return $stateChange;
    }

    /**
     * @return null|UserInterface
     */
    private function getUser()
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return;
        }

        if (!is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return;
        }

        return $user;
    }
}
