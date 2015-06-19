<?php

namespace StateMachine\Event;

final class Events
{
    const EVENT_ON_GUARD = 'statemachine.events.on_guard';
    const EVENT_PRE_TRANSITION = 'statemachine.events.pre_transition';
    const EVENT_POST_TRANSITION = 'statemachine.events.post_transition';
}
