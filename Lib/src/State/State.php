<?php
namespace StateMachine\State;

class State implements StateInterface
{
    private $type;

    private $name;

    private $transitions;

    public function __construct($name, array $transitions, $type = StateInterface::TYPE_NORMAL)
    {
        $this->name = $name;
        $this->transitions = $transitions;
        $this->type = $type;
    }

    public function isInitial()
    {
        return $this->type === StateInterface::TYPE_INITIAL;
    }

    public function isFinal()
    {
        return $this->type === StateInterface::TYPE_FINAL;
    }

    public function isNormal()
    {
        return $this->type === StateInterface::TYPE_NORMAL;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTransitions()
    {
        return $this->transitions;
    }
}
