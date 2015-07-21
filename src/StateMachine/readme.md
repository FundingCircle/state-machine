## Scope
- State machine core logic
- History
- Event dispatcher

## Usage
```php
$state_machine = new StateMachine();
$state_machine->addState('new');
$state_machine->addState('open');
$state_machine->addState('closed');
$state_machine->addTransition('new', 'open');
$state_machine->boot();
```
# Transition to a state
```php
$state_machine->transitionTo('closed');
```



