<?php

namespace StateMachine\Tests\Entity;

use StateMachine\StateMachine\StatefulInterface;
use StateMachine\Traits\StatefulTrait;

class Order implements StatefulInterface
{
    use StatefulTrait;
    private $state;
    private $id;
    private $someValue;

    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSomeValue()
    {
        return $this->someValue;
    }

    /**
     * @param string $someValue
     */
    public function setSomeValue($someValue)
    {
        $this->someValue = $someValue;
    }
}
