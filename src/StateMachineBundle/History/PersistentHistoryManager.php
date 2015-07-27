<?php
namespace StateMachineBundle\History;

use StateMachine\Exception\StateMachineException;
use StateMachine\History\History;
use StateMachine\History\HistoryManagerInterface;
use StateMachine\StateMachine\StatefulInterface;
use StateMachine\StateMachine\StateMachineHistoryInterface;
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
    public function load(StatefulInterface $statefulObject)
    {
        $stateMachine = $statefulObject->getStateMachine();
        if ($stateMachine instanceof StateMachineHistoryInterface) {
            $om = $this->registry->getManager(get_class($statefulObject));
            $stateChanges = $om->getRepository($stateMachine->getHistoryClass())->findBy(
                [
                    'objectIdentifier' => $stateMachine->getObject()->getId()
                ],
                [
                    'createdAt' => 'desc'
                ]
            );

            foreach ($stateChanges as $stateChange) {
                $stateMachine->getHistory()->add($stateChange);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function add(StatefulInterface $object, History $stateChange)
    {
        $options = $stateChange->getOptions();
        $om = $this->registry->getManager(get_class($object));

        if ($stateChange instanceof BlameableStateChangeInterface) {
            $user = $this->tokenStorage->getToken()->getUser();
            if (!$user instanceof UserInterface) {
                throw new StateMachineException(
                    "Unable to write statemachine history, because there's no logged in user"
                );
            }
            $stateChange->setUser($user);
        }

        $om->persist($stateChange);
        if (isset($options['flush']) && $options['flush'] == true) {
            $om->flush($stateChange);
        }

        $object->getStateMachine()->getHistory()->add($stateChange);

        return $stateChange;
    }
}
