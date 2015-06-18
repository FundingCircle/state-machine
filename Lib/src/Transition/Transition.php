<?php

namespace StateMachine\Transition;

class Transition implements TransitionInterface
{
    private $from;

    private $to;

    private $guards;

    private $name;

    /**
     * @return string
     **/
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return string
     **/
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @return \Closure[]
     **/
    public function getGuards()
    {
        return $this->guards;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
