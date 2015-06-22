<?php

namespace StateMachine\Tests\Entity;

use StateMachine\State\StatefulInterface;
use StateMachine\Traits\StatefulTrait;

class Bid implements StatefulInterface
{
    use StatefulTrait;
    private $state;

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
}
