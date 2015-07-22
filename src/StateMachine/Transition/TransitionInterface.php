<?php

namespace StateMachine\Transition;

use StateMachine\State\StateInterface;

interface TransitionInterface
{
    const EDGE_SYMBOL = '::';

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
     * @return int
     */
    public function getObjectIdentifier();

    /**
     * @param int $identifier
     */
    public function setObjectIdentifier($identifier);

    /**
     * @return string
     */
    public function getObjectClass();

    /**
     * @param string $objectClass
     */
    public function setObjectClass($objectClass);

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
    public function getGuards();

    /**
     * @param string $guard
     */
    public function addGuard($guard);

    /**
     * @return string
     */
    public function getFailedCallback();

    /**
     * @param string $failedCallback
     */
    public function setFailedCallback($failedCallback);

    /**
     * @return boolean
     */
    public function isPassed();

    /**
     * @param boolean $passed
     */
    public function setPassed($passed);

    /**
     * @return array
     */
    public function getMessages();

    /**
     * @param string $message
     */
    public function addMessage($message);
}
