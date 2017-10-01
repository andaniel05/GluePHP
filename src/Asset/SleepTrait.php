<?php

namespace Andaniel05\GluePHP\Asset;

trait SleepTrait
{
    public function __sleep()
    {
        return ['id', 'groups', 'dependencies', 'used', 'page', 'minimized'];
    }
}
