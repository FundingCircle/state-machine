<?php

namespace StateMachine\History;

use Symfony\Component\EventDispatcher\Event;

class History extends Event
{
    /** @var  string */
    protected $objectIdentifier;

    /** @var  string */
    protected $failedCallBack;

    /** @var  array */
    protected $messages;

    /** @var  boolean */
    protected $passed;

    /** @var  string */
    protected $fromState;

    /** @var  string */
    protected $toState;

    /** @var  string */
    protected $eventName;

    /** @var  array */
    protected $guards;

    /** @var  array */
    protected $preTransitions;

    /** @var  array */
    protected $postTransitions;

    /** @var  array */
    protected $options;

    public function __construct()
    {
        $this->guards = [];
        $this->preTransitions = [];
        $this->postTransitions = [];
        $this->failedCallBack = '';
    }

    /**
     * @return string
     */
    public function getObjectIdentifier()
    {
        return $this->objectIdentifier;
    }

    /**
     * @param string $objectIdentifier
     */
    public function setObjectIdentifier($objectIdentifier)
    {
        $this->objectIdentifier = $objectIdentifier;
    }

    /**
     * @return string
     */
    public function getFailedCallBack()
    {
        return $this->failedCallBack;
    }

    /**
     * @param string $failedCallBack
     */
    public function setFailedCallBack($failedCallBack)
    {
        $this->failedCallBack = $failedCallBack;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param array $messages
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;
    }

    /**
     * @return boolean
     */
    public function isPassed()
    {
        return $this->passed;
    }

    /**
     * @param boolean $passed
     */
    public function setPassed($passed)
    {
        $this->passed = $passed;
    }

    /**
     * @return string
     */
    public function getFromState()
    {
        return $this->fromState;
    }

    /**
     * @param string $fromState
     */
    public function setFromState($fromState)
    {
        $this->fromState = $fromState;
    }

    /**
     * @return string
     */
    public function getToState()
    {
        return $this->toState;
    }

    /**
     * @param string $toState
     */
    public function setToState($toState)
    {
        $this->toState = $toState;
    }

    /**
     * @return string
     */
    public function getEventName()
    {
        return $this->eventName;
    }

    /**
     * @param string $eventName
     */
    public function setEventName($eventName)
    {
        $this->eventName = $eventName;
    }

    /**
     * @return array
     */
    public function getGuards()
    {
        return $this->guards;
    }

    /**
     * @param array $guards
     */
    public function setGuards($guards)
    {
        $this->guards = $guards;
    }

    /**
     * @return array
     */
    public function getPreTransitions()
    {
        return $this->preTransitions;
    }

    /**
     * @param array $preTransitions
     */
    public function setPreTransitions($preTransitions)
    {
        $this->preTransitions = $preTransitions;
    }

    /**
     * @return array
     */
    public function getPostTransitions()
    {
        return $this->postTransitions;
    }

    /**
     * @param array $postTransitions
     */
    public function setPostTransitions($postTransitions)
    {
        $this->postTransitions = $postTransitions;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }
}
