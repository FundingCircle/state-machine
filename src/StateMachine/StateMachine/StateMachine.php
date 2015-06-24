<?php
namespace StateMachine\StateMachine;

use StateMachine\Accessor\StateAccessor;
use StateMachine\Accessor\StateAccessorInterface;
use StateMachine\Event\Events;
use StateMachine\Event\TransitionEvent;
use StateMachine\Exception\StateMachineException;
use StateMachine\History\HistoryCollection;
use StateMachine\Listener\HistoryListener;
use StateMachine\Listener\HistoryListenerInterface;
use StateMachine\State\State;
use StateMachine\State\StatefulInterface;
use StateMachine\State\StateInterface;
use StateMachine\Transition\TransitionInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class StateMachine
 * @package StateMachine\StateMachine
 * @TODO Add logging support
 */
class StateMachine implements StateMachineInterface, StateMachineHistoryInterface
{
    /** @var StatefulInterface */
    private $object;

    /** @var StateAccessorInterface */
    private $stateAccessor;

    /** @var  HistoryListenerInterface */
    private $historyListener;

    /** @var StateInterface */
    private $currentState;

    /** @var TransitionInterface[] */
    private $transitions;

    /** @var array */
    private $states;

    /** @var bool */
    private $booted;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var TransitionInterface[] */
    private $historyCollection;

    /** @var array */
    private $messages;

    private $transitionClass;

    /**
     * @param StatefulInterface        $object
     * @param EventDispatcherInterface $eventDispatcher
     * @param StateAccessorInterface   $stateAccessor
     * @param HistoryListenerInterface $historyListener
     * @param string                   $transitionClass
     */
    public function __construct(
        StatefulInterface $object,
        EventDispatcherInterface $eventDispatcher,
        StateAccessorInterface $stateAccessor = null,
        HistoryListenerInterface $historyListener = null,
        $transitionClass = null
    ) {
        $this->stateAccessor = $stateAccessor ?: new StateAccessor();
        $this->historyListener = $historyListener ?: new HistoryListener();
        $this->transitionClass = $transitionClass ?: 'StateMachine\Transition\Transition';
        $this->object = $object;
        $this->eventDispatcher = $eventDispatcher;
        $this->booted = false;
        $this->object->setStateMachine($this);
        $this->states = [];
        $this->transitions = [];
        $this->messages = [];
        $this->historyCollection = new HistoryCollection();
        //register history listener
        $this->eventDispatcher->addListener(Events::EVENT_HISTORY_CHANGE, [$this->historyListener, 'onHistoryChange']);
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        if ($this->booted) {
            throw new StateMachineException("Statemachine is already booted");
        }
        $state = $this->stateAccessor->getState($this->object);
        //no state found for the object it means it's new instance, set initial state
        if (null === $state || '' == $state) {
            $state = $this->getInitialState();
            if (null == $state) {
                throw new StateMachineException("No initial state is found");
            }
            $this->stateAccessor->setState($this->object, $state->getName());
        }

        $this->boundTransitionsToStates();
        $this->currentState = $state;
        $this->booted = true;
    }

    /**
     * {@inheritdoc}
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentState()
    {
        return $this->currentState;
    }

    /**
     * {@inheritdoc}
     */
    public function addTransition($from = null, $to = null)
    {
        if ($this->booted) {
            throw new StateMachineException("Cannot add more transitions to booted StateMachine");
        }

        if (null == $from) {
            $fromStates = array_keys($this->states);
        } elseif (is_array($from)) {
            $fromStates = $from;
        } else {
            $fromStates = [$from];
        }

        if (null == $to) {
            $toStates = array_keys($this->states);
        } elseif (is_array($to)) {
            $toStates = $to;
        } else {
            $toStates = [$to];
        }

        return $this->createMultiTransition($fromStates, $toStates);
    }

    /**
     * {@inheritdoc}
     */
    public function addState($name, $type = StateInterface::TYPE_NORMAL)
    {
        $initialState = $this->getInitialState();
        if (StateInterface::TYPE_INITIAL == $type && $initialState instanceof StateInterface) {
            throw new StateMachineException(
                sprintf(
                    "Statemachine cannot have more than one initial state, current initial state is (%s)",
                    $initialState
                )
            );
        }
        $state = new State($name, $type);
        $this->states[$name] = $state;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addGuard($transition, $callable)
    {
        if ($this->booted) {
            throw new StateMachineException("Cannot add more guards to booted StateMachine");
        }

        $this->validateTransition($transition);
        $this->transitions[$transition]->addGuard(get_class($callable));
        $this->eventDispatcher->addListener(Events::EVENT_ON_GUARD, $callable);
    }

    /**
     * {@inheritdoc}
     */
    public function addPreTransition($transition, $callable, $priority = 0)
    {
        if ($this->booted) {
            throw new StateMachineException("Cannot add pre-transition to booted StateMachine");
        }

        $this->validateTransition($transition);
        $this->transitions[$transition]->addPreTransition(get_class($callable));
        $this->eventDispatcher->addListener(Events::EVENT_PRE_TRANSITION, $callable);
    }

    /**
     * {@inheritdoc}
     */
    public function addPostTransition($transition, $callable, $priority = 0)
    {
        if ($this->booted) {
            throw new StateMachineException("Cannot add post-transition to booted StateMachine");
        }

        $this->validateTransition($transition);
        $this->transitions[$transition]->addPostTransition(get_class($callable));
        $this->eventDispatcher->addListener(Events::EVENT_POST_TRANSITION, $callable, $priority);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedTransitions()
    {
        if (!$this->booted) {
            throw new StateMachineException("Statemachine is not booted");
        }

        return $this->currentState->getTransitions();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllTransitions()
    {
        return $this->transitions;
    }

    /**
     * {@inheritdoc}
     */
    public function canTransitionTo($state)
    {
        if (!$this->booted) {
            throw new StateMachineException("Statemachine is not booted");
        }

        return in_array($state, $this->currentState->getTransitions());
    }

    /**
     * {@inheritdoc}
     */
    public function transitionTo($state)
    {
        if (!$this->booted) {
            throw new StateMachineException("Statemachine is not booted");
        }

        if (!$this->canTransitionTo($state)) {
            throw new StateMachineException(
                sprintf(
                    "There's no transition defined from (%s) to (%s), allowed transitions to : [ %s ]
                     or previous transition failed check history for more info",
                    $this->currentState->getName(),
                    $state,
                    implode(',', $this->currentState->getTransitions())
                )
            );
        }
        $transitionName = $this->currentState->getName().TransitionInterface::EDGE_SYMBOL.$state;
        $transition = $this->transitions[$transitionName];
        $transitionEvent = new TransitionEvent($this->object, $transition);

        //Execute guards
        /** @var TransitionEvent $transitionEvent */
        $transitionEvent = $this->eventDispatcher->dispatch(Events::EVENT_ON_GUARD, $transitionEvent);
        $this->messages = $transitionEvent->getMessages();

        if ($transitionEvent->isTransitionRejected()) {
            $this->dispatchHistoryChange($transitionEvent);

            return false;
        }
        //Execute pre transitions
        $transitionEvent = $this->eventDispatcher->dispatch(Events::EVENT_PRE_TRANSITION, $transitionEvent);
        $this->messages = $transitionEvent->getMessages();

        if ($transitionEvent->isTransitionRejected()) {
            $this->dispatchHistoryChange($transitionEvent);

            return false;
        }

        //change state
        $this->currentState = $this->states[$state];
        $this->stateAccessor->setState($this->object, $state);

        //Execute post transitions
        $this->eventDispatcher->dispatch(Events::EVENT_POST_TRANSITION, $transitionEvent);
        $this->messages = $transitionEvent->getMessages();

        $this->dispatchHistoryChange($transitionEvent);

        return true;
    }

    //History implementation
    /**
     * {@inheritdoc}
     */
    public function getHistory()
    {
        return $this->historyCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastTransition()
    {
        return $this->historyCollection->last() ?: null;
    }

    /**
     * Find the initial state in the state machine
     * @return StateInterface
     */
    private function getInitialState()
    {
        /** @var StateInterface $state */
        foreach ($this->states as $state) {
            if ($state->isInitial()) {
                return $state;
            }
        }
    }

    /**
     * Add transitions to states, triggered after booting
     */
    private function boundTransitionsToStates()
    {
        /** @var StateInterface $state */
        foreach ($this->states as $state) {
            $allowedTransitions = [];
            $allowedTransitionsObjects = [];
            /** @var TransitionInterface $transition */
            foreach ($this->transitions as $transition) {
                if ($transition->getFromState()->getName() == $state->getName()) {
                    $allowedTransitionsObjects[] = $transition;
                    $allowedTransitions [] = $transition->getToState()->getName();
                }
            }
            $state->setTransitions($allowedTransitions);
            $state->setTransitionObjects($allowedTransitionsObjects);
        }
    }

    /**
     * @param string $transitionName
     *
     * @throws StateMachineException
     */
    private function validateTransition($transitionName)
    {
        if (!isset($this->transitions[$transitionName])) {
            throw new StateMachineException(
                sprintf(
                    "Transition (%s) is not found, allowed transitions [%s]",
                    $transitionName,
                    implode(',', array_keys($this->transitions))
                )
            );
        }
    }

    /**
     * @param string $state
     *
     * @throws StateMachineException
     */
    private function validateState($state)
    {
        if (isset($state) && !isset($this->states[$state])) {
            throw new StateMachineException(
                sprintf(
                    "State with name: %s is not found, states available are: %s",
                    $state,
                    implode(',', $this->states)
                )
            );
        }
    }

    /**
     * @param string $from
     * @param string $to
     *
     * @return TransitionInterface
     * @throws StateMachineException
     */
    private function createTransition($from, $to)
    {
        $this->validateState($from);
        $this->validateState($to);
        /** @var TransitionInterface $transition */
        $transition = new $this->transitionClass($this->states[$from], $this->states[$to]);
        $this->transitions[$transition->getName()] = $transition;

        return $transition;
    }

    /**
     * @param string[] $fromStates
     * @param string[] $toStates
     *
     * @return TransitionInterface[]
     */
    private function createMultiTransition(array $fromStates, array $toStates)
    {
        $addedTransitions = [];

        foreach ($fromStates as $fromState) {
            foreach ($toStates as $toState) {
                if ($fromState !== $toState) {
                    $addedTransitions[] = $this->createTransition($fromState, $toState);
                }
            }
        }

        return $addedTransitions;
    }

    /**
     * Triggers history change
     *
     * @param TransitionEvent $transitionEvent
     */
    private function dispatchHistoryChange(TransitionEvent $transitionEvent)
    {
        $this->eventDispatcher->dispatch(Events::EVENT_HISTORY_CHANGE, $transitionEvent);
    }
}
