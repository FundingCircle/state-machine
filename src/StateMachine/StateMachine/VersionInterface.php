<?php

namespace StateMachine\StateMachine;

interface VersionInterface
{
    /**
     * @return int
     */
    public function getVersion();

    /**
     * @param int $version
     */
    public function setVersion($version);
}
