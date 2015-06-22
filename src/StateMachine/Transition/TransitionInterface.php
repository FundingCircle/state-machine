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
}
