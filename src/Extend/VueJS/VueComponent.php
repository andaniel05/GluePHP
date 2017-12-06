<?php

namespace Andaniel05\GluePHP\Extend\VueJS;

use Andaniel05\GluePHP\Processor\ShortEventsProcessor;
use Andaniel05\GluePHP\Component\AbstractComponent;

class VueComponent extends AbstractComponent
{
    public function processors(): array
    {
        return [
            VueProcessor::class,
            ShortEventsProcessor::class,
        ];
    }

    public function html(): ?string
    {
    }
}
