<?php

namespace StateMachineBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Registry;
use StateMachine\StateMachine\StatefulInterface;
use StateMachine\StateMachine\StateMachine;
use StateMachine\StateMachine\StateMachineInterface;
use StateMachine\Transition\TransitionInterface;
use StateMachineBundle\StateMachine\StateMachineManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class StateMachineCommand extends Command
{
    /** @var StateMachineManager */
    private $stateMachineManager;

    /** @var  Registry */
    private $registry;

    /** @var  array */
    private $idsMap;

    /**
     * StateMachineCommand constructor.
     *
     * @param StateMachineManager $stateMachineManager
     * @param Registry            $registry
     */
    public function __construct(StateMachineManager $stateMachineManager, Registry $registry)
    {
        $this->stateMachineManager = $stateMachineManager;
        $this->registry = $registry;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('state-machine:trigger')
            ->setDescription('Trigger state machine events/states interactively');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $question = new ChoiceQuestion('Select State Machine', $this->getDefinitions());
        $choice = $helper->ask($input, $output, $question);

        $question = new Question(
            'Enter version of ' . $choice . ' Statemachine (Default: ' . StateMachine::DEFAULT_VERSION . '): ',
            StateMachine::DEFAULT_VERSION
        );
        $version = $helper->ask($input, $output, $question);

        $definitionId = $this->idsMap[$choice];
        $definition = $this->stateMachineManager->getDefinition($definitionId, $version);
        $class = $definition['object']['class'];

        $question = new Question('Enter the object id ?', null);
        $question->setValidator(
            function ($value) {
                if (trim($value) == '') {
                    throw new \Exception('Object id is not specified');
                }

                return $value;
            }
        );

        $objectId = $helper->ask($input, $output, $question);
        $objectManager = $this->registry->getManagerForClass($class);
        /** @var StatefulInterface $statefulObject */
        $statefulObject = $objectManager->getRepository($class)->find($objectId);

        if (null == $statefulObject) {
            throw new \Exception(sprintf('Object with id: %s, for class %s cannot be found', $objectId, $class));
        }

        $stateMachine = $statefulObject->getStateMachine();
        if ($stateMachine->getCurrentState()->isFinal()) {
            throw new \Exception(
                sprintf('Object is in final state, (%s)', $stateMachine->getCurrentState()->getName())
            );
        }
        $question = new ChoiceQuestion('Select trigger', [1 => 'event', 2 => 'to state'], 'event');
        $question->setValidator(
            function ($value) {
                if (trim($value) == '') {
                    throw new \Exception('Trigger type is not specified');
                }

                return $value;
            }
        );
        $oldState = $stateMachine->getCurrentState()->getName();
        $triggerType = (int) $helper->ask($input, $output, $question);

        if (1 == $triggerType) {
            $allowedEvents = array_combine(
                array_values($stateMachine->getAllowedEvents()),
                array_values($stateMachine->getAllowedEvents())
            );

            $question = new ChoiceQuestion(
                'Select event',
                $allowedEvents
            );
            $eventName = $helper->ask($input, $output, $question);
            $question->setValidator(
                function ($value) {
                    if (trim($value) == '') {
                        throw new \Exception('Event name is not specified');
                    }

                    return $value;
                }
            );
            $success = $statefulObject->getStateMachine()->triggers($eventName);
        }

        if (2 == $triggerType) {
            $statesArray = $this->formatAllowedStates($stateMachine);
            $question = new ChoiceQuestion('Select state', $statesArray);
            $state = $helper->ask($input, $output, $question);
            $question->setValidator(
                function ($value) {
                    if (trim($value) == '') {
                        throw new \Exception('Destination state is not specified');
                    }

                    return $value;
                }
            );
            $success = $statefulObject->getStateMachine()->transitionTo($state);
        }

        $table = new Table($output);
        $style = new TableStyle();

        if ($success) {
            $style
                ->setHorizontalBorderChar('<fg=green>-</>')
            ;
        } else {
            $style
                ->setHorizontalBorderChar('<fg=red>-</>')
            ;
        }

        $table->setStyle($style);
        $table->addRow(['status', $success ? 'SUCCESS' : 'FAILED']);
        $table->addRow(['old state', $oldState]);
        $table->addRow(['new state', $stateMachine->getCurrentState()->getName()]);
        $table->addRow(['messages', implode(',', $stateMachine->getMessages())]);

        $table->render();
    }

    /**
     * @return array
     */
    private function getDefinitions()
    {
        $choices = [];
        $definitions = $this->stateMachineManager->getDefinitions();
        $i = 1;
        foreach ($definitions as $definition) {
            foreach ($definition as $version => $definitionDetails) {
                $choices[$i] = $definitionDetails['description'];
                $this->idsMap[$definitionDetails['description']] = $definitionDetails['id'];
                ++$i;
            }
        }

        return $choices;
    }

    private function formatAllowedStates(StateMachineInterface $stateMachine)
    {
        $statesArray = [];
        /** @var TransitionInterface $transition */
        foreach ($stateMachine->getCurrentState()->getTransitionObjects() as $transition) {
            $statesArray[$transition->getToState()->getName()] = $transition->getToState()->getName();
        }

        return $statesArray;
    }
}
