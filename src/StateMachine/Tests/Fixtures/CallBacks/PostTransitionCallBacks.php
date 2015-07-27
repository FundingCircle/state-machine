<?php

namespace StateMachine\Tests\Fixtures\CallBacks;

use StateMachine\Event\TransitionEvent;

class PostTransitionCallBacks
{
    public function succeedCallBack(TransitionEvent $transitionEvent)
    {
        //do nothing
    }

    public function failedCallBack(TransitionEvent $transitionEvent)
    {
        $transitionEvent->addMessage('I am failed post-transition');
        $transitionEvent->rejectTransition($this);
    }
}
