<?php

namespace StateMachine\StateMachine;

use StateMachine\History\History;
use StateMachine\History\HistoryCollection;
use StateMachine\State\StateInterface;
use StateMachine\Transition\TransitionInterface;

interface StateMachineInterface
{
    /**
     * @return array
     */
    public function getStates();

    /**
     * @return array
     */
    public function getTransitions();

    /**
     * @param boolean $triggers whether to trigger events on boot or no
     */
    public function boot($triggers = true);

    /** @return bool */
    public function isBooted();

    /**
     * @return StatefulInterface
     */
    public function getObject();

    /**
     * @return StateInterface
     */
    public function getCurrentState();

    /**
     * Identify unique name for stateMachine.
     *
     * @return mixed
     */
    public function getName();

    /**
     * Define a new transition to the statemachine.
     *
     * @param mixed  $from
     * @param mixed  $to
     * @param string $eventName
     *
     * @return $this
     */
    public function addTransition($from, $to, $eventName);

    /**
     * @param string $name
     * @param string $type
     *
     * @return $this
     */
    public function addState($name, $type);

    /**
     * @return TransitionInterface[]
     */
    public function getAllowedTransitions();

    /**
     * Check if it's possible to transit to given state.
     *
     * @param string $state
     * @param bool   $withGuards
     *
     * @return bool
     */
    public function canTransitionTo($state, $withGuards);

    /**
     * Checks if current stateful object has reached this state or not.
     *
     * @param string $state
     *
     * @return bool
     */
    public function hasReached($state);

    /**
     * Transit the object to given state.
     *
     * @param string $state
     * @param array  $options
     *
     * @return bool
     */
    public function transitionTo($state, $options = []);

    /**
     * Triggers a specific event.
     *
     * @param string $eventName
     * @param array  $options
     *
     * @return bool
     */
    public function triggers($eventName, $options = []);

    /**
     * @return array
     */
    public function getAllowedEvents();

    /**
     * @param callable $callable
     * @param mixed    $from
     * @param mixed    $to
     *
     * @return mixed
     */
    public function addGuard($callable, $from = null, $to = null);

    /**
     * @param callable $callable
     * @param mixed    $from
     * @param mixed    $to
     * @param int      $priority
     *
     * @return mixed
     */
    public function addPreTransition($callable, $from, $to, $priority);

    /**
     * @param callable $callable
     * @param mixed    $from
     * @param mixed    $to
     * @param int      $priority
     *
     * @return mixed
     */
    public function addPostTransition($callable, $from, $to, $priority);

    /**
     * @param callable $callable
     * @param mixed    $from
     * @param mixed    $to
     * @param int      $priority
     *
     * @return mixed
     */
    public function addPostCommit($callable, $from, $to, $priority);

    /**
     * Sets the initial callback when init state is set.
     *
     * @param $callable
     */
    public function setInitCallback($callable);

    /**
     * @param ManagerInterface $manager
     */
    public function setManager(ManagerInterface $manager);

    /**
     * @return array
     */
    public function getMessages();

    /**
     * @return HistoryCollection
     */
    public function getHistory();

    /**
     * @return History
     */
    public function getLastStateChange();

    /**
     * @return string
     */
    public function getHistoryClass();
}
