- [Home](https://github.com/zencap/state-machine/blob/master/readme.md)
- [Library](https://github.com/zencap/state-machine/blob/master/src/StateMachine/readme.md)
- [Bundle](https://github.com/zencap/state-machine/blob/master/src/StateMachineBundle/Resources/doc/index.md)

## Scope
- State machine core logic
- History
- Event dispatcher
- Doctrine

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



