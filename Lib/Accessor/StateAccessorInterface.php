<?php

namespace StateMachine\Lib\Accessor;

interface StateAccessorInterface
{
    public function setState(&$object, $value);

    public function getState($object);
}
