<?php

namespace PlatformPHP\GlueApps\Action;

use PlatformPHP\GlueApps\Action\AbstractAction;

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
