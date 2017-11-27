<?php

namespace Andaniel05\GluePHP\Component;

use Andaniel05\GluePHP\Processor\VueProcessor;

class VueComponent extends AbstractComponent
{
    public function processors(): array
    {
        return [
            VueProcessor::class,
        ];
    }

    public function html(): ?string
    {
    }
}
