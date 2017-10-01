<?php

namespace PlatformPHP\GlueApps\Action;

use PlatformPHP\GlueApps\Action\AbstractAction;

class EvalAction extends AbstractAction
{
    public static function handlerScript(): string
    {
        return 'eval(data);';
    }
}
