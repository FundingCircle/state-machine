<?php

namespace StateMachineBundle\Event;

use StateMachine\Event\CallbackWrapperInterface;
use StateMachine\Event\TransitionEvent;
use StateMachine\Helper\StringHelper;
use StateMachine\Logger\Logger;

/**
 * LoggingCallbackWrapper does two things:
 * - it calls the callback that was described in config
 * - it logs the call of callback
 */
class LoggingCallbackWrapper implements CallbackWrapperInterface
{
    /**
     * @var array
     */
    private $callbackConfig;
    /**
     * @var callable
     */
    private $callback;
    /**
     * @var Logger
     */
    private $logger;

    /**
     * LoggingCallbackWrapper constructor.
     * @param array    $callbackConfig
     * @param callable $callback
     * @param Logger   $logger
     */
    public function __construct(array $callbackConfig, $callback, Logger $logger = null)
    {
        $this->callbackConfig = $callbackConfig;
        $this->callback = $callback;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(TransitionEvent $event, $eventName)
    {
        $result = call_user_func($this->callback, $event, $eventName);

        if ($this->logger) {
            $this->logger->logCallbackCall($event, $eventName, $this->callbackConfig, $result);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return StringHelper::callableToString($this->callback);
    }
}
