<?php

namespace StateMachineBundle\Twig;

use StateMachine\StateMachine\StatefulInterface;
use StateMachine\Visualisation\Configuration;
use StateMachine\Visualisation\Graphviz;

class StateMachineExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('renderGraph', [$this, 'renderGraphFilter'], ['is_safe' => ['html']]),
        ];
    }

    public function renderGraphFilter(StatefulInterface $statefulObject)
    {
        $config = new Configuration(false, 'red');
        $graphviz = new Graphviz($config);

        return $graphviz->render($statefulObject->getStateMachine());
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'statemachine';
    }
}
