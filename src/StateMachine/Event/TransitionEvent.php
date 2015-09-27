<?php

// Purpose: allow arbitrary chaining of processing on a transition via the symfony event dispatcher
// Stops event propagation when the transition fails somwhere.
namespace StateMachine\Event;

use StateMachine\Transition\Transition;
use StateMachineBundle\StateMachine\StateMachineFactory;
use Symfony\Component\EventDispatcher\Event;
use StateMachine\StateMachine\StatefulInterface;
use StateMachine\Transition\TransitionInterface;

class TransitionEvent extends Event
{
    /** @var StatefulInterface */
    private $object;

    /** @var TransitionInterface */
    private $transition;

    /** @var  array */
    private $messages;

    /** @var  array */
    private $options = [];

    /** @var string */
    private $failedCallback;

    /** @var bool */
    protected $passed;

    /** @var  StateMachineFactory */
    protected $manager;

    /**
     * @param StatefulInterface   $object
     * @param TransitionInterface $transition
     * @param array               $options
     * @param StateMachineFactory $manager
     */
    public function __construct(
        StatefulInterface $object,
        TransitionInterface $transition = null,
        $options = [],
        $manager = null
    ) {
        $this->object = $object;
        $this->transition = $transition;
        $this->messages = [];
        $this->options = array_merge($this->options, $options);
        $this->manager = $manager;
        $this->passed = true;
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
     * @return bool
     */
    public function isPassed()
    {
        return $this->passed;
    }

    /**
     * @return StateMachineFactory
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @param $callable
     */
    public function rejectTransition($callable)
    {
        $this->failedCallback = Transition::callableToString($callable);
        $this->passed = false;
        $this->stopPropagation();
    }
}
