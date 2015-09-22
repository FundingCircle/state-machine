<?php

namespace StateMachine\Event;

final class Events
{
    const EVENT_ON_BOOT = 'statemachine.events.on_boot';
    const EVENT_ON_GUARD = 'statemachine.events.on_guard';
    const EVENT_ON_INIT = 'statemachine.events.on_init';
    const EVENT_PRE_TRANSITION = 'statemachine.events.pre_transition';
    const EVENT_POST_TRANSITION = 'statemachine.events.post_transition';
    const EVENT_FAIL_TRANSITION = 'statemachine.events.fail_transition';
}
