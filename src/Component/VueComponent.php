<?php

namespace Andaniel05\GluePHP\Component;

use Andaniel05\GluePHP\Processor\{VueProcessor, ShortEventsProcessor};

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
