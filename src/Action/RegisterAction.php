<?php

namespace Andaniel05\GluePHP\Action;

use Andaniel05\GluePHP\Action\AbstractAction;

class RegisterAction extends AbstractAction
{
    public function __construct(string $actionClass, string $handlerId)
    {
        $handler = $actionClass::handlerScriptWrapper();

        parent::__construct([
            'id'      => $handlerId,
            'handler' => $handler,
        ]);
    }

    public static function handlerScript(): string
    {
        return <<<JAVASCRIPT
    var str = 'app.actionHandlers.' + data.id + ' = ' + data.handler;
    eval(str);
JAVASCRIPT;
    }
}
