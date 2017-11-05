<?php

namespace Andaniel05\GluePHP\Tests\Unit\Action;

use Andaniel05\GluePHP\Action\AbstractAction;

class DummyAction1 extends AbstractAction
{
    public static function handlerScript(): string
    {
        return '';
    }
}