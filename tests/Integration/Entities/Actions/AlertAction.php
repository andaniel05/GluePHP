<?php

namespace PlatformPHP\GlueApps\Tests\Integration\Entities\Actions;

use PlatformPHP\GlueApps\Action\AbstractAction;

class AlertAction extends AbstractAction
{
    public static function handlerScript(): string
    {
        return "alert(data);";
    }
}
