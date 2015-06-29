<?php
namespace StateMachine\Visualisation;

use StateMachine\StateMachine\StateMachineInterface;

/**
 * Interface for state machine visualisation strategies.
 *
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
interface VisualisationInterface
{
    public function render(StateMachineInterface $stateMachine);
}
