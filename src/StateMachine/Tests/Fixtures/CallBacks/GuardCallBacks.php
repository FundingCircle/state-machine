<?php
namespace StateMachineBundle\Tests\Fixtures;

use StateMachine\Event\TransitionEvent;

class GuardCallBacks
{
    public function succeedCallBack(TransitionEvent $transitionEvent)
    {
        //do nothing
    }

    public function failedCallBack(TransitionEvent $transitionEvent)
    {
        $transitionEvent->addMessage("I am failed guard");
        $transitionEvent->rejectTransition($this);
    }
}
