<?php

namespace StateMachine\Accessor;

use Symfony\Component\PropertyAccess\PropertyAccessor;
use StateMachine\Exception\StateMachineException;
use StateMachine\StateMachine\StatefulInterface;

class StateAccessor extends PropertyAccessor implements StateAccessorInterface
{
    /** @var string */
    private $property;

    /**
     * @param string $property
     */
    public function __construct($property = 'state')
    {
        parent::__construct();
        $this->property = $property;
    }

    /**
     * {@inheritdoc}
     */
    public function getState(StatefulInterface $object)
    {
        try {
            return $this->getValue($object, $this->property);
        } catch (\Exception $e) {
            throw new StateMachineException(
                sprintf(
                    'Property path "%s" on object "%s" does not exist.',
                    $this->property,
                    get_class($object)
                ),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setState(StatefulInterface &$object, $value)
    {
        $this->setValue($object, $this->property, $value);
    }
}
