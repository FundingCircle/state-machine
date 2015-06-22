<?php

namespace StateMachine\History;

class StateChange implements StateChangeInterface
{
    /** @var string */
    private $transition;
    /** @var array */
    private $preTransitions;
    /** @var array */
    private $postTransitions;
    /** @var array */
    private $guards;
    /** @var boolean */
    private $passed;
    /** @var array */
    private $messages;
    /** @var string */
    private $stateMachine;
    /** @var string */
    private $failedCallBack;

    /**
     * @return string
     */
    public function getTransition()
    {
        return $this->transition;
    }

    /**
     * @param string $transition
     */
    public function setTransition($transition)
    {
        $this->transition = $transition;
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
    public function setPreTransitions(array $preTransitions)
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
    public function setPostTransitions(array $postTransitions)
    {
        $this->postTransitions = $postTransitions;
    }

    public function setGuards(array $guards)
    {
        $this->guards = $guards;
    }

    public function getGuards()
    {
        return $this->guards;
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
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param array $messages
     */
    public function setMessages(array $messages)
    {
        $this->messages = $messages;
    }

    /**
     * @return string
     */
    public function getStateMachine()
    {
        return $this->stateMachine;
    }

    /**
     * @param string $stateMachine
     */
    public function setStateMachine($stateMachine)
    {
        $this->stateMachine = $stateMachine;
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
}
