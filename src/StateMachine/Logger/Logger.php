<?php

namespace StateMachine\Logger;

use Psr\Log\LoggerInterface;
use StateMachine\Event\TransitionEvent;

class Logger implements LoggerInterface
{
    /** @var LoggerInterface */
    private $logger;
    /** @var  bool */
    private $debug;
    /** @var array */
    private $transitionEvents = [];

    /**
     * @param LoggerInterface $logger
     * @param bool            $debug
     */
    public function __construct(LoggerInterface $logger, $debug)
    {
        $this->logger = $logger;
        $this->debug = $debug;
        $this->transitionEvents['success'] = [];
        $this->transitionEvents['failed'] = [];
    }

    /**
     * @param TransitionEvent $transition
     */
    public function logTransitionSucceed(TransitionEvent $transition)
    {
        $this->transitionEvents['success'][] = $transition;
        $this->debug(sprintf('Transition %s has been executed', $transition->getTransition()->getName()));
    }

    /**
     * @param TransitionEvent $transition
     */
    public function logTransitionFailed(TransitionEvent $transition)
    {
        $this->transitionEvents['failed'][] = $transition;
        $this->warning(sprintf('Transition %s filed', $transition->getTransition()->getName()));
    }

    /**
     * @return mixed
     */
    public function getFailedTransitions()
    {
        return $this->transitionEvents['failed'];
    }

    /**
     * @return mixed
     */
    public function getSucceedTransitions()
    {
        return $this->transitionEvents['success'];
    }

    /**
     * {@inheritdoc}
     */
    public function emergency($message, array $context = array())
    {
        return $this->logger->emergency($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function alert($message, array $context = array())
    {
        return $this->logger->alert($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function critical($message, array $context = array())
    {
        return $this->logger->critical($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function error($message, array $context = array())
    {
        return $this->logger->error($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function warning($message, array $context = array())
    {
        return $this->logger->warning($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function notice($message, array $context = array())
    {
        return $this->logger->notice($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function info($message, array $context = array())
    {
        return $this->logger->info($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function debug($message, array $context = array())
    {
        return $this->logger->debug($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = array())
    {
        return $this->logger->log($level, $message, $context);
    }
}
