<?php

namespace StateMachineBundle\Tests\Entity;

use StateMachineBundle\Entity\History;
use StateMachineBundle\Model\BlameableStateChangeInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class BlameableHistory extends History implements BlameableStateChangeInterface
{
    private $user;

    /**
     * @param UserInterface $user
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }
}
