<?php

// Purpose: allow arbitrary chaining of processing on a transition via the symfony event dispatcher
// Stops event propagation when the transition fails somewhere.
namespace StateMachine\Event;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use StateMachine\StateMachine\ManagerInterface;
use StateMachine\StateMachine\PersistentManager;
use StateMachine\Transition\Transition;
use Symfony\Component\EventDispatcher\Event;
use StateMachine\StateMachine\StatefulInterface;
use StateMachine\Transition\TransitionInterface;

class TransitionEvent extends Event
{
    /** @var StatefulInterface */
    private $object;

    /** @var TransitionInterface */
    private $transition;

    /** @var  ManagerInterface */
    private $manager;

    /** @var  array */
    private $messages;

    /** @var  array */
    private $options = [];

    /** @var string */
    private $failedCallback;

    /** @var  PersistentManager */
    private $persistentManager;

    /**
     * @param StatefulInterface   $object
     * @param TransitionInterface $transition
     * @param ManagerInterface    $manager
     * @param PersistentManager   $persistentManager
     * @param array               $options
     * @param array               $messages
     */
    public function __construct(
        StatefulInterface $object,
        TransitionInterface $transition = null,
        ManagerInterface $manager = null,
        PersistentManager $persistentManager = null,
        $options = [],
        $messages = []
    ) {
        $this->object = $object;
        $this->transition = $transition;
        $this->persistentManager = $persistentManager;
        $this->manager = $manager;
        $this->messages = $messages;
        $this->options = array_merge($this->options, $options);
        $this->failedCallback = '';
    }

    /**
     * @return StatefulInterface
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @return TransitionInterface
     */
    public function getTransition()
    {
        return $this->transition;
    }

    /**
     * @return ManagerInterface
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @param string $message
     */
    public function addMessage($message)
    {
        $this->messages[] = $message;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @return callable
     */
    public function getFailedCallback()
    {
        return $this->failedCallback;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return ObjectManager|EntityManager
     */
    public function getObjectManager()
    {
        return $this->persistentManager->getObjectManager();
    }

    public function clearMessages()
    {
        $this->messages = [];
    }

    /**
     * @param $callable
     */
    public function rejectTransition($callable)
    {
        $this->failedCallback = Transition::callableToString($callable);
        $this->stopPropagation();
    }
}
