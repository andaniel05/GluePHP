<?php

namespace PlatformPHP\GlueApps\Tests\Integration\Entities\Components;

use PlatformPHP\GlueApps\Component\AbstractComponent;

class Button extends AbstractComponent
{
    public function html(): ?string
    {
        return "<button g-event=\"click\">{$this->getId()}</button>";
    }
}
