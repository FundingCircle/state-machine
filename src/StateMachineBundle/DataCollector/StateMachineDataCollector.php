<?php

namespace StateMachineBundle\DataCollector;

use StateMachine\Event\TransitionEvent;
use StateMachine\Logger\Logger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class StateMachineDataCollector extends DataCollector
{
    /** @var Logger */
    private $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Collects data for the given Request and Response.
     *
     * @param Request    $request A Request instance
     * @param Response   $response A Response instance
     * @param \Exception $exception An Exception instance
     *
     * @api
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data['succeed_transitions_count'] = count($this->logger->getSucceedTransitions());
        $this->data['failed_transitions_count'] = count($this->logger->getFailedTransitions());
        $this->data['failed_transitions'] = [];
        $this->data['succeed_transitions'] = [];

        /** @var TransitionEvent $transitionEvent */
        foreach ($this->logger->getFailedTransitions() as $transitionEvent) {
            $this->data['failed_transitions'][] = [
                'name' => $transitionEvent->getTransition()->getName(),
                'messages' => $transitionEvent->getMessages(),
                'callbacks' => [
                    'guards' => $transitionEvent->getTransition()->getGuards(),
                    'pre_transitions' => $transitionEvent->getTransition()->getPreTransitions(),
                    'post_transitions' => $transitionEvent->getTransition()->getPostTransitions()
                ]
            ];
        }

        /** @var TransitionEvent $transitionEvent */
        foreach ($this->logger->getSucceedTransitions() as $transitionEvent) {
            $this->data['succeed_transitions'][] = [
                'name' => $transitionEvent->getTransition()->getName(),
                'messages' => $transitionEvent->getMessages(),
                'callbacks' => [
                    'guards' => $transitionEvent->getTransition()->getGuards(),
                    'pre_transitions' => $transitionEvent->getTransition()->getPreTransitions(),
                    'post_transitions' => $transitionEvent->getTransition()->getPostTransitions()
                ]
            ];
        }
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'state_machine';
    }
}
