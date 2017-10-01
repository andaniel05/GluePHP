<?php

namespace PlatformPHP\GlueApps\Tests\Integration\Entities\Components;

use PlatformPHP\GlueApps\Component\AbstractComponent;

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
