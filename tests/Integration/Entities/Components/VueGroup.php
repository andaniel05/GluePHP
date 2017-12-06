<?php

namespace Andaniel05\GluePHP\Tests\Integration\Entities\Components;

use Andaniel05\GluePHP\Extend\VueJS\VueComponent;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class VueGroup extends VueComponent
{
    /**
     * @Glue
     */
    protected $text = '';

    public function html(): ?string
    {
        return "
            <label>{{ text }}</label>
            {$this->renderizeChildren()}
        ";
    }
}
