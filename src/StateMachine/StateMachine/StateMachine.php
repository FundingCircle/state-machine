<?php

namespace StateMachine\StateMachine;

use StateMachine\Accessor\StateAccessor;
use StateMachine\Accessor\StateAccessorInterface;
use StateMachine\Event\Events;
use StateMachine\Event\TransitionEvent;
use StateMachine\Exception\StateMachineException;
use StateMachine\History\HistoryCollection;
use StateMachine\History\History;
use StateMachine\History\HistoryManagerInterface;
use StateMachine\State\State;
use StateMachine\State\StateInterface;
use StateMachine\Transition\Transition;
use StateMachine\Transition\TransitionInterface;
use StateMachine\EventDispatcher\EventDispatcher;
use StateMachineBundle\StateMachine\StateMachineManager;

/**
 * Class StateMachine.
 *
 * @TODO Add logging support
 */
class StateMachine implements StateMachineInterface, StateMachineHistoryInterface
{
    /** @var StatefulInterface */
    private $object;

    /** @var StateAccessorInterface */
    private $stateAccessor;

    /** @var  PersistentManager */
    private $persistentManager;

    /** @var  HistoryManagerInterface */
    private $historyManager;

    /** @var  HistoryCollection */
    private $historyCollection;

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

    /** @var  string */
    private $name;

    /** @var array */
    private $messages = [];

    /** @var  array */
    private $transitionOptions = [];

    /** @var string */
    private $historyClass;

    /** @var  StateMachineManager */
    private $manager;

    /**
     * @param StatefulInterface       $object
     * @param PersistentManager       $persistentManager
     * @param HistoryManagerInterface $historyManager
     * @param StateAccessorInterface  $stateAccessor
     * @param array                   $transitionOptions
     * @param string                  $historyClass
     * @param string                  $name
     */
    public function __construct(
        StatefulInterface $object,
        PersistentManager $persistentManager = null,
        HistoryManagerInterface $historyManager = null,
        StateAccessorInterface $stateAccessor = null,
        $transitionOptions = [],
        $historyClass = null,
        $name = null
    ) {
        $this->object = $object;
        $this->persistentManager = $persistentManager;
        $this->historyManager = $historyManager;
        $this->stateAccessor = $stateAccessor ?: new StateAccessor();
        $this->transitionOptions = $transitionOptions;
        $this->historyClass = $historyClass ?: 'StateMachine\History\History';
        $this->historyCollection = new HistoryCollection();
        $this->booted = false;
        $this->object->setStateMachine($this);
        $this->states = [];
        $this->transitions = [];
        $this->eventDispatcher = new EventDispatcher();
        $this->name = $name ?: get_class($object);
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        if ($this->booted) {
            throw new StateMachineException('Statemachine is already booted');
        }

        //load history
        if (null !== $this->historyManager) {
            $this->historyCollection = $this->historyManager->load($this->object, $this);
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
            && '' !== $state
            && null !== $objectState
            && '' !== $objectState
            && $state->getName() !== $objectState
        ) {
            throw new StateMachineException(
                sprintf(
                    'States conflict, history shows that current state = %s, and object state = %s,
                     manual check is needed for object class %s with id %s',
                    $state,
                    $objectState,
                    get_class($this->object),
                    $this->object->getId()
                )
            );
        }
        //no state found for the object it means it's new instance, set initial state
        if (null === $state || '' == $state || null === $objectState || '' == $objectState) {
            $state = $this->getInitialState();
            if (null == $state) {
                throw new StateMachineException('No initial state is found');
            }
            $this->stateAccessor->setState($this->object, $state->getName());
            // Assign the transitions to the states to be able to get allowed transitions easily
            $this->bindTransitionsToStates();
            $this->currentState = &$state;

            // prevent booting twice
            $this->booted = true;
            //Dispatch init state event
            $transitionEvent = new TransitionEvent($this->object, null, $this->manager, []);
            $this->eventDispatcher->dispatch(Events::EVENT_ON_INIT, $transitionEvent);
        } else {
            // Assign the transitions to the states to be able to get allowed transitions easily
            $this->bindTransitionsToStates();
            $this->currentState = &$state;

            // prevent booting twice
            $this->booted = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isBooted()
    {
        return $this->booted;
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
     * {@inheritdoc}
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function addTransition($from = null, $to = null, $eventName = null)
    {
        if ($this->booted) {
            throw new StateMachineException('Cannot add more transitions to booted StateMachine');
        }

        $fromStates = $this->resolveStates($from);
        $toStates = $this->resolveStates($to);

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
                    'Statemachine cannot have more than one initial state, current initial state is (%s)',
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
    public function addGuard($callable, $from = null, $to = null)
    {
        if ($this->booted) {
            throw new StateMachineException('Cannot add more guards to booted StateMachine');
        }
        foreach ($this->getTransitionsByStates($from, $to) as $transition) {
            $transition->addGuard($callable);
            $this->eventDispatcher->addListener($transition->getName().'_'.Events::EVENT_ON_GUARD, $callable);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addPreTransition($callable, $from = null, $to = null, $priority = 0)
    {
        if ($this->booted) {
            throw new StateMachineException('Cannot add pre-transition to booted StateMachine');
        }

        foreach ($this->getTransitionsByStates($from, $to) as $transition) {
            $transition->addPreTransition($callable);
            $this->eventDispatcher->addListener(
                $transition->getName().'_'.Events::EVENT_PRE_TRANSITION,
                $callable,
                $priority
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addPostTransition($callable, $from = null, $to = null, $priority = 0)
    {
        if ($this->booted) {
            throw new StateMachineException('Cannot add post-transition to booted StateMachine');
        }

        foreach ($this->getTransitionsByStates($from, $to) as $transition) {
            $transition->addPostTransition($callable);
            $this->eventDispatcher->addListener(
                $transition->getName().'_'.Events::EVENT_POST_TRANSITION,
                $callable,
                $priority
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setInitCallback($callable)
    {
        if ($this->booted) {
            throw new StateMachineException('Cannot set init callback to booted StateMachine');
        }
        $this->eventDispatcher->addListener(
            Events::EVENT_ON_INIT,
            $callable
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedTransitions()
    {
        if (!$this->booted) {
            throw new StateMachineException('Statemachine is not booted');
        }

        return $this->currentState->getTransitions();
    }

    public function getAllowedEvents()
    {
        if (!$this->booted) {
            throw new StateMachineException('Statemachine is not booted');
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
    public function hasReached($state)
    {
        if ($this->historyCollection->count() > 0) {
            /** @var History $stateChange */
            foreach ($this->historyCollection->toArray() as $stateChange) {
                if ($stateChange->getToState() == $state) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function canTransitionTo($state, $withGuards = false)
    {
        if (!$this->booted) {
            throw new StateMachineException('Statemachine is not booted');
        }

        $allowedTransition = in_array($state, $this->currentState->getTransitions());
        //check guards if enabled
        if ($withGuards && $allowedTransition) {
            $transitionName = $this->currentState->getName().TransitionInterface::EDGE_SYMBOL.$state;
            $transition = $this->transitions[$transitionName];
            /** @var TransitionEvent $transitionEvent */
            $transitionEvent = new TransitionEvent($this->object, $transition, $this->manager, []);

            $response = $this->eventDispatcher->dispatch(
                $transitionName.'_'.Events::EVENT_ON_GUARD,
                $transitionEvent
            );

            $this->messages = $transitionEvent->getMessages();

            return $response;
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
            throw new StateMachineException('Statemachine is not booted');
        }

        if (!$this->canTransitionTo($state)) {
            $exception = sprintf(
                "There's no transition defined from (%s) to (%s), allowed transitions to : [ %s ]
                     or previous transition failed check history for more info",
                $this->currentState->getName(),
                $state,
                implode(',', $this->currentState->getTransitions())
            );

            throw new StateMachineException($exception);
        }

        $transitionName = $this->currentState->getName().TransitionInterface::EDGE_SYMBOL.$state;
        $transition = $this->transitions[$transitionName];
        $transitionEvent = new TransitionEvent($this->object, $transition, $this->manager, $options);

        //Execute guards
        /* @var TransitionEvent $transitionEvent */
        $response = $this->eventDispatcher->dispatch(
            $transitionName.'_'.Events::EVENT_ON_GUARD,
            $transitionEvent
        );

        if (!$response) {
            $this->messages = $transitionEvent->getMessages();

            return false;
        }
        try {
            if (null !== $this->persistentManager) {
                $this->persistentManager->beginTransaction($transitionEvent);
            }
            //Execute transition pre-transitions callbacks
            $this->eventDispatcher->dispatch(
                $transitionName.'_'.Events::EVENT_PRE_TRANSITION,
                $transitionEvent
            );
            $this->messages = $transitionEvent->getMessages();

            //if target state is defined, commit and move to the target state
            if (null !== $transitionEvent->getTargetState()) {
                if (null !== $this->persistentManager) {
                    $this->persistentManager->commitTransaction($transitionEvent);
                }
                $this->transitionTo($transitionEvent->getTargetState(), $options);

                return true;
            }

            //change state
            $this->currentState = $this->states[$state];
            $this->stateAccessor->setState($this->object, $state);

            //Execute transition post-transitions callbacks
            $this->eventDispatcher->dispatch(
                $transitionName.'_'.Events::EVENT_POST_TRANSITION,
                $transitionEvent
            );

            //save history
            if (null !== $this->persistentManager) {
                //commit changes to database
                $this->persistentManager->commitTransaction($transitionEvent);
            }


        } catch (\Exception $e) {
            if (null !== $this->persistentManager) {
                $this->persistentManager->rollBackTransaction($transitionEvent);
            }
            throw $e;
        }

        $this->saveHistory($transitionEvent);

        return true;
        //@TODO execute callbacks after_commit
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
     * {@inheritdoc}
     */
    public function setManager(ManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Find the initial state in the state machine.
     *
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
     * Add transitions to states, triggered after booting.
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
     * @param string $state
     *
     * @throws StateMachineException
     */
    private function validateState($state)
    {
        if (isset($state) && !in_array($state, $this->states)) {
            throw new StateMachineException(
                sprintf(
                    'State with name: %s is not found, states available are: %s',
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
     *
     * @throws StateMachineException
     */
    private function createTransition($from, $to, $eventName = null)
    {
        $this->validateState($from);
        $this->validateState($to);

        /** @var TransitionInterface $transition */
        $transition = new Transition($this->states[$from], $this->states[$to], $eventName);
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
                $addedTransitions[] = $this->createTransition($fromState, $toState, $eventName);
            }
        }

        return $addedTransitions;
    }

    /**
     * Triggers history change.
     *
     * @param TransitionEvent $transitionEvent
     */
    private function saveHistory(TransitionEvent $transitionEvent)
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
        $stateChangeEvent->setMessages($transitionEvent->getMessages());
        $stateChangeEvent->setObjectIdentifier($transitionEvent->getObject()->getId());
        $stateChangeEvent->setOptions($transitionEvent->getOptions());

        $this->historyCollection->add($stateChangeEvent);
        if (null !== $this->historyManager) {
            $this->historyManager->add($this->object, $stateChangeEvent);
        }
    }

    /**
     * Returns all transitions between two states, null refers to all states.
     *
     * @param null $from , can be null, array, value
     * @param null $to , can be null, array, value
     *
     * @return TransitionInterface[]
     */
    private function getTransitionsByStates($from = null, $to = null)
    {
        $matchedTransitions = [];
        $fromStates = $this->resolveStates($from);
        $toStates = $this->resolveStates($to);

        foreach ($this->transitions as $transition) {
            foreach ($fromStates as $fromState) {
                foreach ($toStates as $toState) {
                    $this->validateState($toState);
                    $this->validateState($fromState);
                    if ($transition->getFromState()->getName() == $fromState &&
                        $transition->getToState()->getName() == $toState
                    ) {
                        $matchedTransitions[] = $transition;
                    }
                }
            }
        }

        return $matchedTransitions;
    }

    /**
     * Resolves state to array
     * if null => all states
     * if one state => return array with 1 item
     * if array of states => return as it's.
     *
     * @param string $state
     *
     * @return array
     */
    private function resolveStates($state)
    {
        if (null == $state) {
            $states = array_keys($this->states);
        } elseif (is_array($state)) {
            $states = $state;
        } else {
            $states = [$state];
        }

        return $states;
    }

}
