<?php

namespace StateMachineBundle\Controller;

use StateMachine\Visualisation\Graphviz;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use StateMachine\Visualisation\Configuration;

class RendererController extends Controller
{
    public function renderAllAction()
    {
        $stateMachineFactory = $this->get('statemachine.manager');
        $definitions = $stateMachineFactory->getAll();
        $graphs = [];
        foreach ($definitions as $class => $definition) {
            $object = new $class();
            $config = new Configuration(false, 'red');
            $graphviz = new Graphviz($config);

            $stateMachine = $stateMachineFactory->get($object);
            $stateMachine->boot();
            $graphs[$class] = $graphviz->render($stateMachine);
        }

        return $this->render(
            'StateMachineBundle::state_machines.html.twig',
            [
                'graphs' => $graphs,
                'definitions' => $definitions,
                'base_template' => $this->getParameter('statemachine.template_layout'),
            ]
        );
    }
}
