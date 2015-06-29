<?php
namespace StateMachine\Tests\Fixtures\CallBacks;

use StateMachine\Event\TransitionEvent;

class PreTransitionCallBacks
{
    public function succeedCallBack(TransitionEvent $transitionEvent)
    {
        //do nothing
    }

    public function failedCallBack(TransitionEvent $transitionEvent)
    {
        $transitionEvent->addMessage("I am failed pre-transition");
        $transitionEvent->rejectTransition($this);
    }
}
