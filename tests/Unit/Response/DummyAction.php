<?php

namespace PlatformPHP\GlueApps\Tests\Unit\Response;

use PlatformPHP\GlueApps\Action\AbstractAction;

class DummyAction extends AbstractAction
{
    public static function handlerScript(): string
    {
        return '';
    }
}
