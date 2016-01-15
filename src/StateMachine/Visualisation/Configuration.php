<?php

namespace StateMachine\Visualisation;

/**
 * Configuration class for rendering state machine graphs.
 */
class Configuration
{
    /** @var array */
    private $renderProperties;

    /**
     * @param array $renderProperties
     */
    public function __construct($renderProperties = [])
    {
        $this->renderProperties = array_merge(
            [
                'show_callbacks' => false,
                'guard_color' => '#993300',
                'pre_transition_color' => '#3399FF',
                'post_transition_color' => '#0033CC',
                'full_class_name' => false,
                'current_state_color' => '#84bbc6',
                'skip_transition_states' => [
                    ['name' => 'canceled', 'color' => '#d39c3f'],
                    ['name' => 'rejected', 'color' => '#8eb021'],
                ],
                'show_skipped_transitions' => false,
            ],
            $renderProperties
        );
    }

    /**
     * @param $property
     *
     * @return mixed
     */
    public function get($property)
    {
        return $this->renderProperties[$property];
    }

    public function getSkippedTransitionStatesNames()
    {
        $filtered = [];

        if (!$this->get('show_skipped_transitions')) {
            foreach ($this->get('skip_transition_states') as $state) {
                $filtered[] = $state['name'];
            }
        }

        return $filtered;
    }

    public function getSkippedTransitionStateConfig($stateName)
    {
        if (!$this->get('show_skipped_transitions')) {
            foreach ($this->get('skip_transition_states') as $state) {
                if ($state['name'] == $stateName) {
                    return $state;
                }
            }
        }

        return;
    }
}
