<?php
namespace StateMachineBundle\Model;

use Symfony\Component\Security\Core\User\UserInterface;

interface TransitionBlameableInterface
{
    /**
     * @param UserInterface $user
     */
    public function setUser(UserInterface $user);

    /**
     * @return UserInterface
     */
    public function getUser();
}
