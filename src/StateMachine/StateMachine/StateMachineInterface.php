<?php
namespace StateMachine\StateMachine;

use StateMachine\State\StatefulInterface;
use StateMachine\Transition\TransitionInterface;

interface StateMachineInterface
{
    /**
     *
     * @return void
     */
    public function boot();

    /**
     * @return StatefulInterface
     */
    public function getObject();

    /**
     * @return string
     */
    public function getCurrentState();

    /**
     * Define a new transition to the statemachine
     *
     * @param $from
     * @param $to
     *
     * @return $this
     */
    public function addTransition($from, $to);

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
     * @return array
     */
    public function getAllTransitions();

    /**
     * Check if it's possible to transit to given state
     *
     * @param string $state
     *
     * @return bool
     */
    public function canTransitionTo($state);

    /**
     * Transit the object to given state
     *
     * @param string $state
     *
     * @return bool
     */
    public function transitionTo($state);

    /**
     * @param string   $transition
     * @param callable $callable
     */
    public function addGuard($transition, \Closure $callable);

    /**
     * @param string   $transition
     * @param callable $callable
     * @param string   $priority
     *
     */
    public function addPreTransition($transition, \Closure $callable, $priority);

    /**
     * @param string   $transition
     * @param callable $callable
     * @param string   $priority
     *
     */
    public function addPostTransition($transition, \Closure $callable, $priority);
}
