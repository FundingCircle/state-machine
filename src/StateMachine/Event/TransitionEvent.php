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

    /**
     * @param StatefulInterface   $object
     * @param TransitionInterface $transition
     */
    public function __construct(StatefulInterface $object, TransitionInterface $transition)
    {
        $this->object = $object;
        $this->transition = $transition;
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
}
