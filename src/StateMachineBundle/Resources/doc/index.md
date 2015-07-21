- [Home](https://github.com/zencap/state-machine/blob/master/readme.md)
- [Library](https://github.com/zencap/state-machine/blob/master/src/StateMachine/readme.md)
- [Bundle](https://github.com/zencap/state-machine/blob/master/src/StateMachineBundle/Resources/doc/index.md)

## Scope
- SF configuration support for yml files
- Doctrine integrations
  - Wrapping transition into one transaction
  - Persistent history
- User integration, blameable behavior
- Renderer
  - GraphicZ integration

## Installation

### Step 1: Download FOSUserBundle using composer

Add FOSUserBundle by running the command:

``` bash
$ php composer.phar require zencap/state-machine
```

### Step 2: Enable the bundle

Enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new StateMachineBundle\StateMachineBundle(),
    );
}
```
### Step 3: Create transition class

TBD

## Usage

TBD

## More

### Blameable

To track which user modify stateful objects

### Rendering
### Manual flushing
