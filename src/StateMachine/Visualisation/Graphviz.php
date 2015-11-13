<?php

namespace StateMachine\Visualisation;

use StateMachine\StateMachine\StateMachineInterface;
use StateMachine\State\StateInterface;
use StateMachine\Transition\TransitionInterface;

/**
 * Visualisation of a State machine using Graphviz.
 *
 * This class geneates dot source code which can be rendered
 * by graphviz. Pass a configuration object to control how
 * the nodes are rendered.
 *
 * @link   http://www.graphviz.org/Gallery/directed/fsm.gv.txt
 *
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class Graphviz implements VisualisationInterface
{
    /**
     * the graphviz graph representation.
     *
     * @var \Alom\Graphviz\Digraph
     */
    private $graph;

    /**
     * visualisation options.
     *
     * @var Configuration
     */
    private $configuration;

    /**
     * Constructor.
     *
     * @param Configuration $config
     */
    public function __construct(Configuration $config = null)
    {
        if (null === $config) {
            $config = new Configuration();
        }
        $this->configuration = $config;
    }

    /**
     * @param StateMachineInterface $stateMachine
     *
     * @return string
     */
    public function render(StateMachineInterface $stateMachine)
    {
        $this->graph = new Digraph('state_machine');
        $this->addNodes($stateMachine);
        $this->addEdges($stateMachine);
        $this->graph->end();
        $dotSource = $this->graph->render();

        return $this->parseSvg($dotSource);
    }

    /**
     * Adds the states as nodes.
     *
     * @param StateMachineInterface $stateMachine
     */
    private function addNodes(StateMachineInterface $stateMachine)
    {
        $states = $stateMachine->getStates();
        foreach ($states as $name => $state) {
            $this->graph->beginNode($name, $this->getNodeAttributes($stateMachine, $state))->end();
        }
    }

    /**
     * Returns the node attributes.
     *
     * @param StateMachineInterface $stateMachine
     * @param string                $state
     *
     * @return array
     */
    private function getNodeAttributes(StateMachineInterface $stateMachine, $state)
    {
        /* @var $state StateInterface */
        $data = array(
            'shape' => $state->getType() != StateInterface::TYPE_NORMAL ? 'box' : 'box',
            'label' => $this->getNodeLabel($state),
            'fontsize' => '10.0',
        );
        if ($stateMachine->getCurrentState() == $state && $this->configuration->get('current_state_color')) {
            $data['fillcolor'] = $this->configuration->get('current_state_color');
            $data['style'] = 'filled';
        }

        return $data;
    }

    /**
     * Returns the node label.
     *
     * @param StateInterface $state
     *
     * @return string
     */
    private function getNodeLabel(StateInterface $state)
    {
        return $state->getName();
    }

    /**
     * Adds all transitions as edges.
     *
     * @param StateMachineInterface $stateMachine
     */
    private function addEdges(StateMachineInterface $stateMachine)
    {
        $states = $stateMachine->getStates();
        foreach ($states as $name => $state) {
            /* @var $state StateInterface */
            $transitions = $state->getTransitionObjects();

            //@TODO show callbacks on edges
            foreach ($transitions as $trans) {
                /* @var $trans TransitionInterface */
                $this->graph->beginEdge(
                    [
                        $state->getName(),
                        $trans->getToState()->getName(),
                    ],
                    [
                        'label' => $this->renderLabel($trans, $stateMachine),
                        'fontsize' => '10.0',
                    ]
                )
                    ->end();
            }
        }
    }

    /**
     * Parse SVG from dot source.
     *
     * @param string $dotString
     *
     * @return string
     */
    private function parseSvg($dotString)
    {
        $descriptorSpec = array(
            0 => array('pipe', 'r'), // stdin
            1 => array('pipe', 'w'), // stdout
            2 => array('pipe', 'a'), // stderr
        );

        $process = proc_open('dot -Tsvg', $descriptorSpec, $pipes);

        if (is_resource($process)) {
            fwrite($pipes[0], $dotString);
            fclose($pipes[0]);

            $svg = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            proc_close($process);
            $svg = explode('<svg ', $svg);

            // has it worked out?
            if (count($svg) < 2) {
                return '';
            }

            return '<svg '.$svg[1];
        }

        return '';
    }

    protected function renderLabel(TransitionInterface $trans, StateMachineInterface $stateMachine)
    {
        $transitionName = $trans->getEventName() ?: $trans->getName();

        $nodeLabel = '<TR><TD ALIGN="LEFT"><B>'.$transitionName.'</B></TD></TR>';
        $nodeLabel .= $this->renderAdditionalLabelInformation($trans);
        $complete = '<<TABLE BORDER="0" CELLBORDER="0" CELLSPACING="0">'.$nodeLabel.'</TABLE>>';

        return $complete;
    }

    protected function renderAdditionalLabelInformation(TransitionInterface $transition)
    {
        $additional = '';
        // build the markup for callbacks
        if ($this->configuration->get('show_callbacks')) {
            if (!empty($transition->getGuards())) {
                $additional .= '<TR><TD ALIGN="LEFT">Guards: </TD></TR>';
            }
            foreach ($transition->getGuards() as $guard) {
                $displayClassName =
                    '<FONT COLOR="' . $this->configuration->get('guard_color')
                    . '">' . $this->renderClassName($guard)
                    . '</FONT>';
                $additional .= '<TR><TD ALIGN="LEFT">    +' . $displayClassName . '</TD></TR>';
            }

            if (!empty($transition->getPreTransitions())) {
                $additional .= '<TR><TD ALIGN="LEFT">PreTransitions: </TD></TR>';
            }

            foreach ($transition->getPreTransitions() as $preTransition) {
                $displayClassName =
                    '<FONT COLOR="' . $this->configuration->get('pre_transition_color')
                    . '">' . $this->renderClassName($preTransition)
                    . '</FONT>';
                $additional .= '<TR><TD ALIGN="LEFT">    +' . $displayClassName . '</TD></TR>';
            }

            if (!empty($transition->getPostTransitions())) {
                $additional .= '<TR><TD ALIGN="LEFT">PostTransitions: </TD></TR>';
            }

            foreach ($transition->getPostTransitions() as $postTransition) {
                $displayClassName =
                    '<FONT COLOR="' . $this->configuration->get('post_transition_color')
                    . '">' . $this->renderClassName($postTransition)
                    . '</FONT>';
                $additional .= '<TR><TD ALIGN="LEFT">    +' . $displayClassName . '</TD></TR>';
            }
        }

        return $additional;
    }

    /**
     * Render the class name.
     *
     * @param $className
     *
     * @return mixed|string
     */
    protected function renderClassName($className)
    {
        $className = addslashes($className);
        if (!$this->configuration->get('full_class_name')) {
            $parts = explode('\\', $className);
            $className = end($parts);
        }

        return $className;
    }
}
