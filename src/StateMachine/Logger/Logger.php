<?php

namespace StateMachine\Logger;

use Psr\Log\LoggerInterface;
use StateMachine\Event\TransitionEvent;

class Logger implements LoggerInterface
{
    /** @var LoggerInterface */
    private $logger;
    /** @var array */
    private $transitionEvents = [];

    /**
     * @param LoggerInterface $logger
     * @param bool            $debug
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->transitionEvents['success'] = [];
        $this->transitionEvents['failed'] = [];
    }

    /**
     * @param TransitionEvent $transition
     */
    public function logTransitionSucceed(TransitionEvent $transition)
    {
        $this->transitionEvents['success'][] = $transition;
        $this->info(
            "Transition {$transition->getTransition()->getName()} has been executed",
            $this->buildContext($transition)
        );
    }

    /**
     * @param TransitionEvent $transition
     */
    public function logTransitionFailed(TransitionEvent $transition)
    {
        $this->transitionEvents['failed'][] = $transition;
        $this->warning("Transition {$transition->getTransition()->getName()} failed", $this->buildContext($transition));
    }

    /**
     * @param TransitionEvent $transition
     * @param string          $event
     * @param array           $callback
     * @param mixed           $callbackResult
     */
    public function logCallbackCall(TransitionEvent $transition, $event, $callback, $callbackResult)
    {
        $message = "Callback method was called";
        $context = $this->buildContext($transition, $event, $callback, $callbackResult);
        $callbackResult === false
            ? $this->warning($message, $context)
            : $this->debug($message, $context);
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

    /**
     * @param TransitionEvent $transition
     * @param string|null     $event
     * @param array|null      $callback
     * @param mixed           $result
     *
     * @return array
     */
    private function buildContext(TransitionEvent $transition, $event = null, array $callback = null, $result = null)
    {
        $context = [
            'object_id' => $transition->getObject()->getId(),
            'object_class' => get_class($transition->getObject()),
            'messages' => $transition->getMessages(),
        ];

        if (null !== $event) {
            $context['event'] = $event;
        }

        if (null !== $result) {
            $context['result'] = is_object($result)
                ? ['class' => get_class($object), 'id' => $object->getId()]
                : $result;
        }

        if (null !== $callback) {
            $context['callback'] = sprintf('%s::%s', $callback['callback'], $callback['method']);
        }

        return $context;
    }
}
