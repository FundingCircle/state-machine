<?php

namespace StateMachineBundle\Tests\Listeners;

use StateMachine\Event\TransitionEvent;

class MockListener
{
    public function onGuardFailed(TransitionEvent $transitionEvent)
    {
        return false;
    }

    public function onGuardSuccess(TransitionEvent $transitionEvent)
    {
        return true;
    }

    public function postTransitionFailed(TransitionEvent $transitionEvent)
    {
        return false;
    }

    public function postTransitionSuccess(TransitionEvent $transitionEvent)
    {
        return true;
    }

    public static function simpleCallback(TransitionEvent $transitionEvent)
    {
        return true;
    }
}
