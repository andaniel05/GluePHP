<?php

namespace Andaniel05\GluePHP\Tests\Integration\Entities\Components;

use Andaniel05\GluePHP\Component\AbstractComponent;

class Button extends AbstractComponent
{
    public function html(): ?string
    {
        return "<button gphp-event=\"click\">{$this->getId()}</button>";
    }
}
