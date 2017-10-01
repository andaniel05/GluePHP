<?php

namespace PlatformPHP\GlueApps\Tests\Unit\Component;

use PlatformPHP\GlueApps\Component\AbstractComponent;

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
