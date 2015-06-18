<?php

namespace StateMachine\Transition;

interface TransitionInterface
{
    /**
     * @return string
     **/
    public function getFrom();

    /**
     * @return string
     **/
    public function getTo();

    /**
     * @return \Closure[]
     **/
    public function getGuards();

    /**
     * @return string
     */
    public function getName();
}
