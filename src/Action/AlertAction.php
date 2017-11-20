<?php

namespace Andaniel05\GluePHP\Action;

use Andaniel05\GluePHP\Action\AbstractAction;

class AlertAction extends AbstractAction
{
    public static function handlerScript(): string
    {
        return "alert(data);";
    }
}
