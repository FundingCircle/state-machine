<?php

namespace StateMachine\StateMachine;

use StateMachine\EventDispatcher\EventDispatcher;
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
     */
    public function boot();

    /** @return bool */
    public function isBooted();

    /**
     * @return StatefulInterface
     */
    public function getObject();

    /**
     * @return string
     */
    public function getCurrentState();

    /**
     * @return EventDispatcher
     */
    public function getEventDispatcher();

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
     *
     * @return bool
     */
    public function triggers($eventName);

    /**
     * @return array
     */
    public function getAllowedEvents();

    /**
     * @param string   $transition
     * @param callable $callable
     */
    public function addGuard($transition, $callable);

    /**
     * @param string   $transition
     * @param callable $callable
     * @param string   $priority
     */
    public function addPreTransition($transition, $callable, $priority);

    /**
     * @param string   $transition
     * @param callable $callable
     * @param string   $priority
     */
    public function addPostTransition($transition, $callable, $priority);
}
