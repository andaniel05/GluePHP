<?php

namespace Andaniel05\GluePHP\Tests\Integration\Entities\Components;

use Andaniel05\GluePHP\Component\AbstractComponent;

class TextInput extends AbstractComponent
{
    /**
     * @Glue
     */
    public $text = '';

    public function html(): ?string
    {
        return '<input type="text" g-bind="text">';
    }
}
