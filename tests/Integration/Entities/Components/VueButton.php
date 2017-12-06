<?php

namespace Andaniel05\GluePHP\Tests\Integration\Entities\Components;

use Andaniel05\GluePHP\Extend\VueJS\VueComponent;

class VueButton extends VueComponent
{
    /**
     * @Glue
     */
    protected $text = '';

    public function html(): ?string
    {
        return '<button @click>{{ text }}</button>';
    }
}
