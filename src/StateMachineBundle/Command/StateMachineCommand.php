<?php

namespace StateMachineBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Registry;
use StateMachine\StateMachine\StatefulInterface;
use StateMachine\StateMachine\StateMachineInterface;
use StateMachine\StateMachine\VersionInterface;
use StateMachine\Transition\TransitionInterface;
use StateMachineBundle\StateMachine\StateMachineManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class StateMachineCommand extends Command
{
    const TRIGGER_TYPES = [1 => 'event', 2 => 'to state'];

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
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'Object PK')
            ->addOption('class', null, InputOption::VALUE_REQUIRED, $this->getClassOptionDescription())
            ->addOption('trigger', null, InputOption::VALUE_REQUIRED, $this->getTriggerOptionDescription())
            ->addOption('sm-version', null, InputOption::VALUE_REQUIRED)
            ->addOption('state', null, InputOption::VALUE_REQUIRED)
            ->addOption('event', null, InputOption::VALUE_REQUIRED)
            ->setDescription('Trigger state machine events/states interactively');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $objectId = (int)$input->getOption('id');
        $classDefinition = $input->getOption('class');
        $triggerType = $input->getOption('trigger');
        $version = $input->getOption('sm-version');
        $state = $input->getOption('state');
        $eventName = $input->getOption('event');

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        if (!$classDefinition) {
            $question = new ChoiceQuestion('Select State Machine', $this->getDefinitions());
            $choice = $helper->ask($input, $output, $question);
        } else {
            $definitions = $this->getDefinitions();
            $choice = $definitions[$classDefinition];
        }
        $definitionId = $this->idsMap[$choice];
        if (!$version) {
            $question = new Question(
                sprintf('Enter version of %s Statemachine (Default: %d):', $choice, VersionInterface::DEFAULT_VERSION),
                VersionInterface::DEFAULT_VERSION
            );
            $version = $helper->ask($input, $output, $question);
        }

        $definition = $this->stateMachineManager->getDefinition($definitionId, $version);
        $class = $definition['object']['class'];

        if ($objectId <= 0) {
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
        }

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

        $oldState = $stateMachine->getCurrentState()->getName();
        if (!$triggerType) {
            $question = new ChoiceQuestion('Select trigger', self::TRIGGER_TYPES, 'event');
            $question->setValidator(
                function ($value) {
                    if (trim($value) == '') {
                        throw new \Exception('Trigger type is not specified');
                    }

                    return $value;
                }
            );
            $triggerType = (int)$helper->ask($input, $output, $question);
        }

        if (1 == $triggerType) {
            if (!$eventName) {
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
            }
            $success = $stateMachine->triggers($eventName);
        }

        if (2 == $triggerType) {
            if (!$state) {
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
            }
            $success = $stateMachine->transitionTo($state);
        }

        $table = new Table($output);
        $style = new TableStyle();

        if ($success) {
            $style
                ->setHorizontalBorderChar('<fg=green>-</>');
        } else {
            $style
                ->setHorizontalBorderChar('<fg=red>-</>');
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

    private function getClassOptionDescription()
    {
        return "Class: \n".join(
            "\n",
            array_map(
                function ($k, $v) {
                    return "$k - $v";
                },
                array_keys($this->getDefinitions()),
                array_values($this->getDefinitions())
            )
        );
    }

    private function getTriggerOptionDescription()
    {
        return 'Trigger type: '.join(
            ',',
            array_map(
                function ($k, $v) {
                    return "$k ($v)";
                },
                array_keys(self::TRIGGER_TYPES),
                array_values(self::TRIGGER_TYPES)
            )
        );
    }
}
