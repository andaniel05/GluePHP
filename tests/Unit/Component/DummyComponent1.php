<?php

namespace Andaniel05\GluePHP\Tests\Unit\Component;

use Andaniel05\GluePHP\Component\AbstractComponent;

class DummyComponent1 extends AbstractComponent
{
    /**
     * @Glue
     */
    protected $attr1;

    /**
     * @Glue()
     */
    protected $attr2;

    /**
     * @Glue(getter="getMyAttr3", setter="setMyAttr3")
     */
    protected $attr3;

    public function html(): string
    {
        return '';
    }

    public function getAttr1()
    {
    }

    public function getAttr2(): string
    {
    }

    public function getMyAttr3(): int
    {
    }
}
