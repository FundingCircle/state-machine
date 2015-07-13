### purpose
State machine core logic
History
Event dispatcher

## Usage
$state_machine = new StateMachine();
$state_machine->addState('new');
$state_machine->addState('open');
$state_machine->addState('closed');
$state_machine->addTransition('new', 'open');
$state_machine->boot();

# Transition to a state
$state_machine->transitionTo('closed');

where is the event that triggers a given transition, e.g. cancel to go from open to cancelled?
Without names for the events, we cannot do automatic UI, showing the buttons.




