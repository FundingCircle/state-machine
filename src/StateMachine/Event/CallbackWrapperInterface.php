<?php

namespace StateMachine\Event;

/**
 * Interface for classes wrapping state machine callbacks to extend their functionality.
 * @package StateMachine\Event
 */
interface CallbackWrapperInterface
{
    public function __invoke(TransitionEvent $event, $eventName);
    public function __toString();
}
