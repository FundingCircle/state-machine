<?php

namespace StateMachine\StateMachine;

interface ManagerInterface
{
    /**
     * Gets and adds new statemachine instance for giving objects.
     *
     * @param StatefulInterface $object
     *
     * @return StateMachine
     */
    public function add(StatefulInterface $object);

    /**
     * Get statemachine instance for giving objects.
     *
     * @param StatefulInterface $object
     *
     * @return StateMachineInterface
     */
    public function get(StatefulInterface $object);
}
