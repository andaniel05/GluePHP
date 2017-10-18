<?php

namespace Andaniel05\GluePHP\Tests\Functional;

use Andaniel05\GluePHP\Tests\TestApp;
use Andaniel05\GluePHP\Tests\StaticTestCase;
use Andaniel05\GluePHP\Action\AbstractAction;
use Andaniel05\GluePHP\Component\AbstractComponent;
use Andaniel05\GluePHP\Processor\AbstractProcessor;

class AbstractAppStaticTest extends StaticTestCase
{
    public function testAnAppInstanceWithVarNameEqualToTheIdIsBuilding()
    {
        $appId = uniqid('app');
        $app = $this->getMockBuilder(TestApp::class)
            ->setConstructorArgs([''])
            ->setMethods(['getId'])
            ->getMock();
        $app->method('getId')->willReturn($appId);

        $this->writeDocument($app->html());

        $this->assertTrue($this->driver->executeScript("return $appId instanceof GluePHP.App"));
    }

    public function testTheFrontEndAppIsInDebugWhenBackEndAppIsInDebug()
    {
        $appId = uniqid('app');
        $app = $this->getMockBuilder(TestApp::class)
            ->setConstructorArgs([''])
            ->setMethods(['getId'])
            ->getMock();
        $app->method('getId')->willReturn($appId);

        $app->setDebug();
        $this->writeDocument($app->html());

        $this->assertTrue($this->driver->executeScript("return $appId.debug"));
    }

    public function testTheUrlOfTheFrontAppIsTheControllerPath()
    {
        $path = uniqid('http://localhost/controller.php');
        $app = new TestApp($path);

        $this->writeDocument($app->html());

        $this->assertEquals($path, $this->script('return app.url'));
    }

    public function testTheTokenOfTheFrontAppIsEqualToTheBackendAppToken()
    {
        $token = uniqid('token');
        $app = $this->getMockBuilder(TestApp::class)
            ->setConstructorArgs([''])
            ->setMethods(['getToken'])
            ->getMock();
        $app->method('getToken')->willReturn($token);

        $this->writeDocument($app->html());

        $this->assertEquals($token, $this->script('return app.token'));
    }

    public function testTheFrontEndActionHandlersAreCreated()
    {
        $action1 = new class([]) extends AbstractAction {

            public static $secret;

            public static function handlerScript(): string
            {
                $secret = static::$secret;
                return "return '$secret';";
            }
        };

        $actionClass1 = get_class($action1);
        $actionClass1::$secret = $secret = uniqid();

        $this->app->registerActionClass($actionClass1, 'action1');

        $this->writeDocument($this->app->html());

        $script = "return app.actionHandlers['action1']()";
        $this->assertEquals($secret, $this->script($script));
    }

    public function testAllComponentClassesAreCreated()
    {
        $component1 = getDummyComponent('component1');
        $class1 = get_class($component1);
        $frontClassId = uniqid('Component1');

        $this->app->registerComponentClass($class1, $frontClassId);

        $this->writeDocument($this->app->html());

        $script = "var obj1 = new app.componentClasses['$frontClassId']();";
        $script .= "return obj1 instanceof GluePHP.Component";
        $this->assertTrue($this->script($script));
    }

    public function testTheProcessorsAreRegisteredInTheFrontEnd()
    {
        $value = uniqid();
        $processor = new class($value) extends AbstractProcessor {

            public static $value;

            public function __construct($value)
            {
                static::$value = $value;
            }

            public static function script(): string
            {
                $value = static::$value;
                return "component.secret = '{$value}';";
            }
        };

        $processorClass = get_class($processor);
        $component = new class('component', $processorClass) extends AbstractComponent {

            public function __construct($id, $processorClass)
            {
                parent::__construct($id);

                $this->processorClass = $processorClass;
            }


            public function processors(): array
            {
                return [$this->processorClass];
            }
        };

        $this->body->addChild($component);
        $this->app->registerProcessorClass(get_class($processor));
        $this->writeDocument($this->app->html());

        $this->assertEquals(
            $value, $this->script("return app.getComponent('component').secret")
        );
    }
}
