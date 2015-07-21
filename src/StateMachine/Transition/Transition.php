<?php

namespace StateMachine\Transition;

use StateMachine\State\StateInterface;

class Transition implements TransitionInterface
{
    /** @var StateInterface */
    protected $fromState;
    /** @var StateInterface */
    protected $toState;
    /** @var string */
    protected $name;
    /** @var  int */
    protected $objectIdentifier;
    /** @var  string */
    protected $objectClass;
    /** @var array */
    protected $preTransitions;
    /** @var array */
    protected $postTransitions;
    /** @var array */
    protected $guards;
    /** @var  string */
    protected $failedCallback;
    /** @var boolean */
    protected $passed;
    /** @var array */
    protected $messages;

    /**
     * @param StateInterface $fromState
     * @param StateInterface $toState
     */
    public function __construct(StateInterface $fromState = null, StateInterface $toState = null)
    {
        $this->fromState = $fromState;
        $this->toState = $toState;
    }

    /**
     * {@inheritdoc}
     */
    public function getFromState()
    {
        return $this->fromState;
    }

    /**
     * {@inheritdoc}
     */
    public function getToState()
    {
        return $this->toState;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->fromState->getName().static::EDGE_SYMBOL.$this->toState->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectIdentifier()
    {
        return $this->objectIdentifier;
    }

    /**
     * {@inheritdoc}
     */
    public function setObjectIdentifier($objectIdentifier)
    {
        $this->objectIdentifier = $objectIdentifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectClass()
    {
        return $this->objectClass;
    }

    /**
     * {@inheritdoc}
     */
    public function setObjectClass($objectClass)
    {
        $this->objectClass = $objectClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getPreTransitions()
    {
        return $this->preTransitions;
    }

    /**
     * {@inheritdoc}
     */
    public function addPreTransition($preTransition)
    {
        $this->preTransitions[] = $preTransition;
    }

    /**
     * {@inheritdoc}
     */
    public function getPostTransitions()
    {
        return $this->postTransitions;
    }

    /**
     * {@inheritdoc}
     */
    public function addPostTransition($postTransition)
    {
        $this->postTransitions[] = $postTransition;
    }

    /**
     * {@inheritdoc}
     */
    public function getGuards()
    {
        return $this->guards;
    }

    /**
     * {@inheritdoc}
     */
    public function addGuard($guard)
    {
        $this->guards[] = $guard;
    }

    /**
     * {@inheritdoc}
     */
    public function getFailedCallback()
    {
        return $this->failedCallback;
    }

    /**
     * {@inheritdoc}
     */
    public function setFailedCallback($failedCallback)
    {
        $this->failedCallback = $failedCallback;
    }

    /**
     * {@inheritdoc}
     */
    public function isPassed()
    {
        return $this->passed;
    }

    /**
     * {@inheritdoc}
     */
    public function setPassed($passed)
    {
        $this->passed = $passed;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * {@inheritdoc}
     */
    public function addMessage($message)
    {
        $this->messages[] = $message;
    }
}
