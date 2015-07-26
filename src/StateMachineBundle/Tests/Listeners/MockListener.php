<?php

namespace StateMachineBundle\Tests\Listeners;

use StateMachine\Event\TransitionEvent;

class MockListener
{
    public function postTransitionFailed(TransitionEvent $transitionEvent)
    {

    }

    public function postTransitionSuccess(TransitionEvent $transitionEvent)
    {

    }
}
