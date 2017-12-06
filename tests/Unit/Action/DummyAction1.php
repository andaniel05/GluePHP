<?php

namespace Andaniel05\GluePHP\Tests\Unit\Action;

use Andaniel05\GluePHP\Action\AbstractAction;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class DummyAction1 extends AbstractAction
{
    public static function handlerScript(): string
    {
        return '';
    }
}
