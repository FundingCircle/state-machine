<?php
namespace StateMachine\Lib\Accessor;

use StateMachine\Lib\Exception\StateMachineException;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class StateAccessor extends PropertyAccessor implements StateAccessorInterface
{
    private $property;

    public function __construct($property)
    {
        $this->property = $property;
    }

    public function getState($object)
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

    public function setState(&$object, $value)
    {
        try {
            $this->setValue($object, $this->property, $value);
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
}
