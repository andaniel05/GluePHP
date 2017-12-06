<?php

namespace Andaniel05\GluePHP\Tests\Unit\Component;

use Andaniel05\GluePHP\Component\AbstractComponent;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class DummyComponent2 extends AbstractComponent
{
    /**
     * @Glue()
     */
    protected $attr4;

    public function html(): string
    {
        return '';
    }

    public function getAttr4()
    {
    }
}
