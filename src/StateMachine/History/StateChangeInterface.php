<?php


namespace StateMachine\History;

interface StateChangeInterface
{
    public function setStateMachine($stateMachine);

    public function getStateMachine();

    public function setTransition($transition);

    public function getTransition();

    public function setPreTransitions(array $preTransitions);

    public function getPreTransitions();

    public function setPostTransitions(array $postTransitions);

    public function getPostTransitions();

    public function setGuards(array $guards);

    public function getGuards();

    public function setFailedCallBack($failedCallBack);

    public function getFailedCallBack();

    public function setPassed($result);

    public function isPassed();

    public function setMessages(array $messages);

    public function getMessages();
}
