<?php

namespace Andaniel05\GluePHP\Tests\Unit\Response;

use Andaniel05\GluePHP\Action\AbstractAction;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class DummyAction extends AbstractAction
{
    public static function handlerScript(): string
    {
        return '';
    }
}
