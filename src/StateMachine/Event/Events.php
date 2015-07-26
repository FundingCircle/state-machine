<?php

// Defines Events for the Symfony event dispatcher
// TODO: do we reallyl want to run this through the event dispatcher? What do we gain from that?

namespace StateMachine\Event;

final class Events
{
    const EVENT_ON_BOOT = 'statemachine.events.on_boot';
    const EVENT_ON_GUARD = 'statemachine.events.on_guard';
    const EVENT_PRE_TRANSITION = 'statemachine.events.pre_transition';
    const EVENT_POST_TRANSITION = 'statemachine.events.post_transition';
    const EVENT_HISTORY_CHANGE = 'statemachine.events.history_change';
}
