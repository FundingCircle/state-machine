<?php

namespace StateMachineBundle\Model;

use Symfony\Component\Security\Core\User\UserInterface;

interface BlameableStateChangeInterface
{
    /**
     * @param UserInterface $user
     */
    public function setUser(UserInterface $user = null);

    /**
     * @return UserInterface
     */
    public function getUser();
}
