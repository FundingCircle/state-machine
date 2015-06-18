<?php

namespace StateMachine\State;

interface StateInterface
{
    const
        TYPE_INITIAL = 'initial',
        TYPE_NORMAL = 'normal',
        TYPE_FINAL = 'final';

    public function isInitial();

    public function isFinal();

    public function isNormal();

    public function getType();

    public function getName();

    public function getTransitions();
}
