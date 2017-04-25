<?php

namespace StateMachine\StateMachine;

interface VersionInterface
{
    const DEFAULT_VERSION = 1;

    /**
     * @return int
     */
    public function getVersion();

    /**
     * @param int $version
     */
    public function setVersion($version);
}
