<?php

// Purpose: allow arbitrary chaining of processing on a transition via the symfony event dispatcher
// Stops event propagation when the transition fails somwhere.
namespace StateMachine\Event;

use Symfony\Component\EventDispatcher\Event;
use StateMachine\State\StatefulInterface;
use StateMachine\Transition\TransitionInterface;

class TransitionEvent extends Event
{
    /** @var StatefulInterface */
    private $object;

    /** @var TransitionInterface */
    private $transition;

    /** @var  array */
    private $messages;

    /** @var string */
    private $failedCallback;

    /** @var  bool */
    private $transitionRejected;

    /** @var  array */
    private $options = [];

    /**
     * @param StatefulInterface   $object
     * @param TransitionInterface $transition
     * @param array               $options
     */
    public function __construct(StatefulInterface $object, TransitionInterface $transition, $options = [])
    {
        $this->object = $object;
        $this->transition = $transition;
        $this->transitionRejected = false;
        $this->messages = [];
        $this->options = array_merge($this->options, $options);
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
     * @param string $message
     */
    public function addMessage($message)
    {
        $this->transition->addMessage($message);
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->transition->getMessages();
    }

    /**
     * @return callable
     */
    public function getFailedCallback()
    {
        return $this->failedCallback;
    }

    /**
     * @return boolean
     */
    public function isTransitionRejected()
    {
        return $this->transitionRejected;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param $callable
     */
    public function rejectTransition($callable)
    {
        //@TODO may be add method too
        $this->failedCallback = get_class($callable);
        $this->transitionRejected = true;
        $this->stopPropagation();
    }
}
