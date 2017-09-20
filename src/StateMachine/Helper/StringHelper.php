<?php

namespace StateMachine\Helper;

use StateMachine\Event\CallbackWrapperInterface;

class StringHelper
{
    /**
     * Resolve callable which can be one of
     *  - closure
     *  - class instance and method
     *  - class path and static method.
     *
     * @TODO this method should move out of this class
     *
     * @param callable $callable
     *
     * @return string
     */
    public static function callableToString($callable)
    {
        if ($callable instanceof \Closure) {
            $callableString = 'closure';
        } elseif ($callable instanceof CallbackWrapperInterface) {
            $callableString = (string) $callable;
        } elseif (is_array($callable) && is_object($callable[0])) {
            $callableString = get_class($callable[0]) . '::' . $callable[1];
        } elseif (is_array($callable)) {
            $callableString = $callable[0] . '::' . $callable[1];
        } else {
            $callableString = get_class($callable);
        }

        return $callableString;
    }
}
