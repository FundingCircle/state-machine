<?php
namespace StateMachine\StateMachine;

use StateMachine\Accessor\StateAccessor;
use StateMachine\Accessor\StateAccessorInterface;
use StateMachine\Exception\StateMachineException;
use StateMachine\State\StatefulInterface;
use StateMachine\Transition\TransitionInterface;

class StateMachine implements StateMachineInterface
{
    private $class;

    private $object;

    private $stateAccessor;

    private $currentState;

    public function __construct(
        $class,
        StateAccessorInterface $stateAccessor = null,
        $property = 'state',
        $object = null
    ) {
        $this->class = $class;
        $this->stateAccessor = $stateAccessor ?: new StateAccessor($property);
        $this->object = $object;
    }

    public function boot()
    {
        if (null === $this->object) {
            throw new StateMachineException(
                sprintf("Cannot boot StateMachine without object, have you forgot to setObject()? ")
            );
        }
        if (get_class($this->object) !== $this->class) {
            throw new StateMachineException(
                sprintf(
                    "StateMachine expected object of class %s instead of %s",
                    $this->class,
                    get_class($this->object)
                )
            );
        }

        $state = $this->stateAccessor->getState($this->object);
        //no state found for the object it means it's new instance, set initial state
        if (null === $state) {
            //@TODO get the initial states from the definition
            $state = '';
            $this->stateAccessor->setState($this->object, $state);
        }
        $this->currentState = $state;
    }

    /**
     * @param StatefulInterface $object
     *
     * @return void
     */
    public function setObject(StatefulInterface $object)
    {
        $this->object = $object;
    }

    /**
     * @return StatefulInterface
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @return string
     */
    public function getCurrentState()
    {
        return $this->currentState;
    }

    /**
     * @param TransitionInterface $transition
     *
     * @return void
     */
    public function addTransition(TransitionInterface $transition)
    {
        // TODO: Implement addTransition() method.
    }

    /**
     * @return TransitionInterface[]
     */
    public function getAllowedTransitions()
    {
        // TODO: Implement getAllowedTransitions() method.
    }

    public function canTransitionTo($state)
    {
        // TODO: Implement canTransitionTo() method.
    }

    public function transitionTo($state)
    {
        // TODO: Implement transitionTo() method.
    }

    public function trigger($transition)
    {
        // TODO: Implement trigger() method.
    }

}
