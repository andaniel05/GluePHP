<?php

namespace Andaniel05\GluePHP\Action;

use Andaniel05\GluePHP\Action\AbstractAction;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class EvalAction extends AbstractAction
{
    public static function handlerScript(): string
    {
        return 'eval(data);';
    }
}
