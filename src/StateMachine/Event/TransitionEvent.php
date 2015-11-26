<?php

// Purpose: allow arbitrary chaining of processing on a transition via the symfony event dispatcher
// Stops event propagation when the transition fails somwhere.
namespace StateMachine\Event;

use StateMachine\StateMachine\ManagerInterface;
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

    /** @var  string */
    private $targetState;


    /**
     * @param StatefulInterface   $object
     * @param TransitionInterface $transition
     * @param ManagerInterface    $manager
     * @param array               $options
     */
    public function __construct(
        StatefulInterface $object,
        TransitionInterface $transition = null,
        ManagerInterface $manager = null,
        $options = []
    ) {
        $this->object = $object;
        $this->transition = $transition;
        $this->manager = $manager;
        $this->messages = [];
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
     * @return string
     */
    public function getTargetState()
    {
        return $this->targetState;
    }

    /**
     * @param string $targetState
     */
    public function setTargetState($targetState)
    {
        $this->targetState = $targetState;
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
