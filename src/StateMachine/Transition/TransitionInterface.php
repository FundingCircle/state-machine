<?php

namespace StateMachine\Transition;

use StateMachine\State\StateInterface;

interface TransitionInterface
{
    const EDGE_SYMBOL = '->';

    /**
     * @return StateInterface
     **/
    public function getFromState();

    /**
     * @return StateInterface
     **/
    public function getToState();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getEventName();

    /**
     * @return array
     */
    public function getPreTransitions();

    /**
     * @param string $preTransition
     */
    public function addPreTransition($preTransition);

    /**
     * @return array
     */
    public function getPostTransitions();

    /**
     * @param string $postTransition
     */
    public function addPostTransition($postTransition);

    /**
     * @return array
     */
    public function getPostCommits();
    /**
     * @param string $postCommit
     */
    public function addPostCommit($postCommit);

    /**
     * @return array
     */
    public function getGuards();

    /**
     * @param string $guard
     */
    public function addGuard($guard);
}
