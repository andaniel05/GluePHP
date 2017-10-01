<?php

namespace Andaniel05\GluePHP\Action;

use Andaniel05\GluePHP\Action\AbstractAction;

class EvalAction extends AbstractAction
{
    public static function handlerScript(): string
    {
        return 'eval(data);';
    }
}
