<?php

namespace StateMachine\Visualisation;

/**
 * Configuration class for rendering state machine graphs
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
                'current_state_color' => 'green'
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
}
