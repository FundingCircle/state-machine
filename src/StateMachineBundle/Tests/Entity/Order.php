<?php

namespace StateMachineBundle\Tests\Entity;

use StateMachine\StateMachine\StatefulInterface;
use StateMachine\Traits\StatefulTrait;

class Order implements StatefulInterface
{
    use StatefulTrait;

    private $id;

    private $state;

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
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}
