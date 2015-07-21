<?php
namespace StateMachineBundle\Entity;

use StateMachine\Transition\Transition as BaseTransition;

class Transition extends BaseTransition
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }
}
