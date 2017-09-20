<?php

namespace StateMachine\Transition;

use StateMachine\Helper\StringHelper;
use StateMachine\State\StateInterface;

class Transition implements TransitionInterface
{
    /** @var StateInterface */
    protected $fromState;
    /** @var StateInterface */
    protected $toState;
    /** @var string */
    protected $name;
    /** @var  string */
    protected $eventName;
    /** @var array */
    protected $preTransitions;
    /** @var array */
    protected $postTransitions;
    /** @var array */
    protected $postCommits;
    /** @var array */
    protected $guards;

    /**
     * @param StateInterface $fromState
     * @param StateInterface $toState
     * @param string         $eventName
     */
    public function __construct(StateInterface $fromState = null, StateInterface $toState = null, $eventName = null)
    {
        $this->fromState = $fromState;
        $this->toState = $toState;
        $this->eventName = $eventName;
        $this->guards = [];
        $this->preTransitions = [];
        $this->postTransitions = [];
        $this->postCommits = [];
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
    public function getEventName()
    {
        return $this->eventName;
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
        $this->preTransitions[] = StringHelper::callableToString($preTransition);
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
        $this->postTransitions[] = StringHelper::callableToString($postTransition);
    }

    /**
     * {@inheritdoc}
     */
    public function addPostCommit($postCommit)
    {
        $this->postCommits[] = StringHelper::callableToString($postCommit);
    }

    /**
     * {@inheritdoc}
     */
    public function getPostCommits()
    {
        return $this->postCommits;
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
        $this->guards[] = StringHelper::callableToString($guard);
    }
}
