<?php

namespace StateMachine\Lib\Transition;

use StateMachine\Lib\State\StateInterface;

interface TransitionInterface
{
    /**
     * @return StateInterface
     **/
    public function getFromState();

    /**
     * @return StateInterface
     **/
    public function getToState();

    /**
     * @return \Closure[]
     **/
    public function getGuards();

    /**
     * @return string
     */
    public function getName();
}
