<?php

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

    /** @var \Closure */
    private $failedCallback;

    /** @var  bool */
    private $transitionRejected;

    /**
     * @param StatefulInterface   $object
     * @param TransitionInterface $transition
     */
    public function __construct(StatefulInterface $object, TransitionInterface $transition)
    {
        $this->object = $object;
        $this->transition = $transition;
        $this->transitionRejected = false;
        $this->messages = [];
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
     * @param callable $failedCallback
     */
    public function setFailedCallback(\Closure $failedCallback)
    {
        $this->failedCallback = $failedCallback;
    }

    /**
     * @return boolean
     */
    public function isTransitionRejected()
    {
        return $this->transitionRejected;
    }

    /**
     * @param $callable
     */
    public function rejectTransition($callable)
    {
        $this->failedCallback = $callable;
        $this->transitionRejected = true;
        $this->stopPropagation();
    }
}
