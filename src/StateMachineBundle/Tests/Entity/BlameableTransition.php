<?php


namespace StateMachineBundle\Tests\Entity;

use StateMachineBundle\Entity\Transition;
use StateMachineBundle\Model\TransitionBlameableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class BlameableTransition extends Transition implements TransitionBlameableInterface
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
