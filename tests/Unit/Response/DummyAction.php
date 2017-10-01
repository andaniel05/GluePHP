<?php

namespace Andaniel05\GluePHP\Tests\Unit\Response;

use Andaniel05\GluePHP\Action\AbstractAction;

class DummyAction extends AbstractAction
{
    public static function handlerScript(): string
    {
        return '';
    }
}
