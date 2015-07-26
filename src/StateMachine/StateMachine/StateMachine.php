<?php
namespace StateMachine\StateMachine;

use StateMachine\Accessor\StateAccessor;
use StateMachine\Accessor\StateAccessorInterface;
use StateMachine\Event\BootEvent;
use StateMachine\Event\Events;
use StateMachine\Event\TransitionEvent;
use StateMachine\Exception\StateMachineException;
use StateMachine\History\HistoryCollection;
use StateMachine\History\History;
use StateMachine\Listener\HistoryListenerInterface;
use StateMachine\State\State;
use StateMachine\State\StatefulInterface;
use StateMachine\State\StateInterface;
use StateMachine\Transition\TransitionInterface;
use StateMachine\EventDispatcher\EventDispatcher;

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

    /** @var TransitionInterface[] */
    private $eventTransitions;

    /** @var array */
    private $states;

    /** @var bool */
    private $booted;

    /** @var EventDispatcher */
    private $eventDispatcher;

    /** @var TransitionInterface[] */
    private $historyCollection;

    /** @var array */
    private $messages = [];

    /** @var string */
    private $transitionClass;

    /** @var  array */
    private $transitionOptions = [];

    /** @var string */
    private $historyClass;

    /**
     * @param StatefulInterface        $object
     * @param StateAccessorInterface   $stateAccessor
     * @param HistoryListenerInterface $historyListener
     * @param string                   $transitionClass
     * @param array                    $transitionOptions
     * @param string                   $historyClass
     * @param EventDispatcher          $eventDispatcher
     */
    public function __construct(
        StatefulInterface $object,
        StateAccessorInterface $stateAccessor = null,
        HistoryListenerInterface $historyListener = null,
        $transitionClass = null,
        $transitionOptions = [],
        $historyClass = null,
        EventDispatcher $eventDispatcher = null
    ) {
        $this->stateAccessor = $stateAccessor ?: new StateAccessor();
        $this->historyListener = $historyListener;
        $this->transitionClass = $transitionClass ?: 'StateMachine\Transition\Transition';
        $this->transitionOptions = $transitionOptions;
        $this->historyClass = $historyClass ?: 'StateMachine\History\History';
        $this->object = $object;
        $this->booted = false;
        $this->object->setStateMachine($this);
        $this->states = [];
        $this->transitions = [];
        $this->messages = [];
        $this->eventDispatcher = $eventDispatcher ?: new EventDispatcher();
        $this->historyCollection = new HistoryCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        if ($this->booted) {
            throw new StateMachineException("Statemachine is already booted");
        }

        $state = null;
        $objectState = $this->stateAccessor->getState($this->object);
        //gets state from history
        if (null !== $this->getLastStateChange()) {
            /** @var StateInterface $state */
            $state = $this->states[$this->getLastStateChange()->getToState()];
        }

        //state exists in history and not the same as object, conflict alert
        if (null !== $state
            && $state->getName() !== $objectState
        ) {
            throw new StateMachineException(
                sprintf(
                    "States conflict, history shows that current state = %s, and object state = %s,
                     manual check is needed for object class %s with id %s",
                    $state,
                    $objectState,
                    get_class($this->object),
                    $this->object->getId()
                )
            );
        }
        //no state found for the object it means it's new instance, set initial state
        if (null === $state || '' == $state) {
            $state = $this->getInitialState();
            if (null == $state) {
                throw new StateMachineException("No initial state is found");
            }
            $this->stateAccessor->setState($this->object, $state->getName());
        }

        // Assign the transitions to the states to be able to get allowed transitions easily
        $this->bindTransitionsToStates();
        $this->currentState = $state;

        // prevent booting twice
        $this->booted = true;

        // register history listener
        if ($this->historyListener instanceof HistoryListenerInterface) {
            $this->eventDispatcher->addListener(
                Events::EVENT_HISTORY_CHANGE,
                [$this->historyListener, 'onHistoryChange']
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getStates()
    {
        return $this->states;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransitions()
    {
        return $this->transitions;
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
     * @return EventDispatcher
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function addTransition($from = null, $to = null, $eventName = null)
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

        return $this->createMultiTransition($fromStates, $toStates, $eventName);
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
        $callableClass = ($callable instanceof \Closure) ? "closure" : get_class($callable[0]);
        $this->transitions[$transition]->addGuard($callableClass);
        $this->eventDispatcher->addListener($transition."_".Events::EVENT_ON_GUARD, $callable);
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
        $callableClass = ($callable instanceof \Closure) ? "closure" : get_class($callable[0]);
        $this->transitions[$transition]->addPreTransition($callableClass);
        $this->eventDispatcher->addListener($transition."_".Events::EVENT_PRE_TRANSITION, $callable);
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
        $callableClass = ($callable instanceof \Closure) ? "closure" : get_class($callable[0]);
        $this->transitions[$transition]->addPostTransition($callableClass);
        $this->eventDispatcher->addListener($transition."_".Events::EVENT_POST_TRANSITION, $callable, $priority);
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

    public function getAllowedEvents()
    {
        if (!$this->booted) {
            throw new StateMachineException("Statemachine is not booted");
        }

        return $this->currentState->getEvents();
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
    public function canTransitionTo($state, $withGuards = false)
    {
        if (!$this->booted) {
            throw new StateMachineException("Statemachine is not booted");
        }

        $allowedTransition = in_array($state, $this->currentState->getTransitions());
        //check guards if enabled
        if ($withGuards && $allowedTransition) {
            $transitionName = $this->currentState->getName().TransitionInterface::EDGE_SYMBOL.$state;
            $transition = $this->transitions[$transitionName];
            /** @var TransitionEvent $transitionEvent */
            $transitionEvent = new TransitionEvent($this->object, $transition);

            return $this->eventDispatcher->dispatch(
                $transitionName."_".Events::EVENT_ON_GUARD,
                $transitionEvent,
                $this->messages
            );


        }

        return $allowedTransition;
    }

    /**
     * {@inheritdoc}
     */
    public function transitionTo($state, $options = [])
    {
        $options = array_merge($this->transitionOptions, $options);
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
        $transitionEvent = new TransitionEvent($this->object, $transition, $options);

        //Execute guards
        /** @var TransitionEvent $transitionEvent */
        $response = $this->eventDispatcher->dispatch(
            $transitionName."_".Events::EVENT_ON_GUARD,
            $transitionEvent,
            $this->messages
        );

        if (!$response) {
            $this->updateTransition($transitionEvent);

            return false;
        }
        //Execute pre transitions
        $response = $this->eventDispatcher->dispatch(
            $transitionName."_".Events::EVENT_PRE_TRANSITION,
            $transitionEvent,
            $this->messages
        );

        if (!$response) {
            $this->updateTransition($transitionEvent);

            return false;
        }

        //change state
        $this->currentState = $this->states[$state];
        $this->stateAccessor->setState($this->object, $state);

        //Execute post transitions
        $this->eventDispatcher->dispatch(
            $transitionName."_".Events::EVENT_POST_TRANSITION,
            $transitionEvent,
            $this->messages
        );

        //@TODO this mainly dispatched for persistent subscriber in the bundle
        //Execute general post transitions
        $this->eventDispatcher->dispatch(
            Events::EVENT_POST_TRANSITION,
            $transitionEvent,
            $this->messages
        );

        $this->updateTransition($transitionEvent);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function triggers($eventName, $options = [])
    {
        $options = array_merge($this->transitionOptions, $options);
        $allowedEvents = $this->getAllowedEvents();

        foreach ($allowedEvents as $transitionName => $event) {
            if ($event == $eventName) {
                $transition = $this->transitions[$transitionName];

                return $this->transitionTo($transition->getToState()->getName(), $options);
            }
        }

        throw new StateMachineException(
            sprintf(
                "Event %s didn't match any transition, allowed events for state %s are [%s]",
                $eventName,
                $this->currentState,
                implode(',', array_keys($this->eventTransitions))

            )
        );
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
    public function getLastStateChange()
    {
        return $this->historyCollection->last() ?: null;
    }

    /**
     * {@inheritdoc}
     */
    public function getHistoryClass()
    {
        return $this->historyClass;
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
    private function bindTransitionsToStates()
    {
        /** @var StateInterface $state */
        foreach ($this->states as $state) {
            $allowedTransitions = [];
            $allowedTransitionsObjects = [];
            $allowedEvents = [];
            /** @var TransitionInterface $transition */
            foreach ($this->transitions as $transition) {
                if ($transition->getFromState()->getName() == $state->getName()) {
                    $allowedTransitionsObjects[] = $transition;
                    $allowedTransitions [] = $transition->getToState()->getName();
                    if (null != $transition->getEventName()) {
                        $allowedEvents[$transition->getName()] = $transition->getEventName();
                    }
                }
            }
            $state->setTransitions($allowedTransitions);
            $state->setTransitionObjects($allowedTransitionsObjects);
            $state->setEvents($allowedEvents);
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
     * @param string $eventName
     *
     * @return TransitionInterface
     * @throws StateMachineException
     */
    private function createTransition($from, $to, $eventName = null)
    {
        $this->validateState($from);
        $this->validateState($to);

        /** @var TransitionInterface $transition */
        $transition = new $this->transitionClass($this->states[$from], $this->states[$to], $eventName);
        //transition getting overridden if it exists already
        $this->transitions[$transition->getName()] = $transition;
        $this->eventTransitions[$eventName] = $transition;

        return $transition;
    }

    /**
     * @param string[] $fromStates
     * @param string[] $toStates
     * @param string   $eventName
     *
     * @return TransitionInterface[]
     */
    private function createMultiTransition(array $fromStates, array $toStates, $eventName = null)
    {
        $addedTransitions = [];

        foreach ($fromStates as $fromState) {
            foreach ($toStates as $toState) {
                if ($fromState !== $toState) {
                    $addedTransitions[] = $this->createTransition($fromState, $toState, $eventName);
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
    private function updateTransition(TransitionEvent $transitionEvent)
    {
        $transition = $transitionEvent->getTransition();
        /** @var History $stateChangeEvent */
        $stateChangeEvent = new $this->historyClass();

        $stateChangeEvent->setEventName($transition->getEventName());
        $stateChangeEvent->setFailedCallBack($transitionEvent->getFailedCallback());
        $stateChangeEvent->setFromState($transition->getFromState()->getName());
        $stateChangeEvent->setToState($transition->getToState()->getName());
        $stateChangeEvent->setGuards($transition->getGuards());
        $stateChangeEvent->setPreTransitions($transition->getPreTransitions());
        $stateChangeEvent->setPostTransitions($transition->getPostTransitions());
        $stateChangeEvent->setPassed($transitionEvent->isPassed());
        $stateChangeEvent->setMessages($transitionEvent->getMessages());
        $stateChangeEvent->setObjectIdentifier($transitionEvent->getObject()->getId());
        $stateChangeEvent->setOptions($transitionEvent->getOptions());

        //add it to history
        $this->historyCollection->add($stateChangeEvent);

        $this->eventDispatcher->dispatch(Events::EVENT_HISTORY_CHANGE, $stateChangeEvent);
    }
}
