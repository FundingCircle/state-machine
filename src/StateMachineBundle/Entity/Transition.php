<?php
namespace StateMachineBundle\Entity;

use StateMachine\Transition\Transition as BaseTransition;

class Transition extends BaseTransition
{
    /**
     * @var int
     */
    protected $id;
}
