<?php

namespace Andaniel05\GluePHP\Tests\Functional;

use Andaniel05\GluePHP\Tests\StaticTestCase;
use Andaniel05\GluePHP\Action\AbstractAction;

class AbstractActionStaticTest extends StaticTestCase
{
    public function testHandlerScriptKnowHisAppInstance()
    {
        $appId = uniqid('app');
        $this->app->setId($appId);

        $action1 = new class([]) extends AbstractAction {

            public static function handlerScript(): string
            {
                return "return app;";
            }
        };

        $actionClass1 = get_class($action1);
        $this->app->registerActionClass($actionClass1, 'action1');

        $this->writeDocument($this->app->html());

        $script = "return $appId == $appId.runAction({ handler: 'action1', data: {} })";
        $this->assertTrue($this->script($script));
    }

    public function testHandlerScriptKnowTheActionData()
    {
        $action1 = new class([]) extends AbstractAction {

            public static function handlerScript(): string
            {
                return "return data;";
            }
        };

        $actionClass1 = get_class($action1);
        $this->app->registerActionClass($actionClass1, 'action1');

        $this->writeDocument($this->app->html());

        $script = "var myData = {};";
        $script .= "return myData == app.runAction({ handler: 'action1', data: myData })";
        $this->assertTrue($this->script($script));
    }
}
