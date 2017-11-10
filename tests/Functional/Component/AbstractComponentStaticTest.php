<?php

namespace Andaniel05\GluePHP\Tests\Functional\Component;

use function Andaniel05\GluePHP\jsVal;
use Andaniel05\GluePHP\Component\AbstractComponent;
use Andaniel05\GluePHP\Component\Model\{ModelInterface, Model};
use Andaniel05\GluePHP\Tests\StaticTestCase;

class AbstractComponentStaticTest extends StaticTestCase
{
    public function testTheComponentsAreCreated()
    {
        $componentId = uniqid('component');
        $component = getDummyComponent($componentId);

        $this->body->addChild($component);
        $this->writeDocument($this->app->html());

        $frontClassName = $this->app->getFrontComponentClass(
            get_class($component)
        );

        $script = "var component = app.getComponent('$componentId');";
        $script .= "return component instanceof app.componentClasses['$frontClassName']";

        $this->assertTrue($this->script($script));
    }

    public function testTheComponentsKnowTheApp()
    {
        $component1 = getDummyComponent('component1');

        $this->body->addChild($component1);
        $this->writeDocument($this->app->html());

        $this->assertTrue($this->script("return app === app.getComponent('component1').app"));
    }

    public function providerTheComponentModelsAreInitializedWithPrimitivesTypes()
    {
        return [
            [uniqid('string')], // string
            [rand()],           // integer
            [frand()],          // float
            [true],             // boolean
            [false],            // boolean
            [null],             // null
        ];
    }

    /**
     * @dataProvider providerTheComponentModelsAreInitializedWithPrimitivesTypes
     */
    public function testTheComponentModelsAreInitializedWithPrimitivesTypes($value)
    {
        $component1 = new class('component1') extends AbstractComponent {

            /**
             * @Glue
             */
            public $attr1;

            public function html(): ?string {return null;}
        };

        $component1->attr1 = $value;
        $this->body->addChild($component1);
        $this->writeDocument($this->app->html());
        $this->script('window.component1 = app.getComponent("component1")');

        $this->assertSame(
            $value, $this->script('return component1.model["attr1"]')
        );
    }

    public function providerThrowInvalidTypeExceptionWhenAttributeTypeIsNotAllowed()
    {
        return [
            [function () {}],
        ];
    }

    /**
     * @expectedException Andaniel05\GluePHP\Component\Model\Exception\InvalidTypeException
     * @dataProvider providerThrowInvalidTypeExceptionWhenAttributeTypeIsNotAllowed
     */
    public function testThrowInvalidTypeExceptionWhenAttributeTypeIsNotAllowed($value)
    {
        $component1 = new class('component1') extends AbstractComponent {

            /**
             * @Glue
             */
            public $attr1;

            public function html(): ?string {return null;}
        };

        $component1->attr1 = $value;
        $this->body->addChild($component1);
        $this->writeDocument($this->app->html());
    }

    public function testModelSettersSetTheAttributeValueAndRegisterAnUpdateInTheApp()
    {
        $componentId = uniqid('component');
        $component = new class($componentId) extends AbstractComponent {

            /**
             * @Glue
             */
            public $attr1;

            public function html(): ?string {return null;}
        };

        $value = uniqid();

        $this->body->addChild($component);
        $this->writeDocument($this->app->html());
        $this->script("window.component = app.getComponent('{$componentId}')");
        $this->script("component.setAttr1('$value')");

        $this->assertEquals(
            $value, $this->script('return component.model["attr1"]')
        );
        $this->assertEquals(
            $value, $this->script("return app.buffer['{$componentId}']['attr1']")
        );
    }

    public function testModelSettersDoNotRegisterAnUpdateInTheAppIfSecondArgumentIsFalse()
    {
        $componentId = uniqid('component');
        $component = new class($componentId) extends AbstractComponent {

            /**
             * @Glue
             */
            public $attr1;

            public function html(): ?string {return null;}
        };

        $value = uniqid();

        $this->body->addChild($component);
        $this->writeDocument($this->app->html());
        $this->script("window.component = app.getComponent('{$componentId}')");
        $this->script("component.setAttr1('$value', false)");

        $this->assertEquals(
            'undefined', $this->script("return typeof(app.buffer['{$componentId}'])")
        );
    }

    public function testArraysAreSupportedInTheJavaScriptModel()
    {
        $component1 = new class('component1') extends AbstractComponent {

            /**
             * @Glue
             */
            public $attr1;

            public function html(): ?string {return null;}
        };

        $array = range(0, rand(0, 10));

        $component1->attr1 = $array;
        $this->body->addChild($component1);
        $this->writeDocument($this->app->html());
        $this->script('window.component1 = app.getComponent("component1")');

        $this->assertEquals($array, $this->script("return component1.model['attr1']"));
    }

    public function testObjectsAreSupportedInTheJavaScriptModel()
    {
        $component1 = new class('component1') extends AbstractComponent {

            /**
             * @Glue
             */
            public $attr1;

            public function html(): ?string {return null;}
        };

        $attribute = uniqid('attribute');
        $value = uniqid();
        $obj = new \stdClass();
        $obj->{$attribute} = $value;

        $component1->attr1 = $obj;
        $this->body->addChild($component1);
        $this->writeDocument($this->app->html());
        $this->script('window.component1 = app.getComponent("component1")');

        $this->assertEquals($value, $this->script("return component1.model['attr1']['{$attribute}']"));
    }

    public function testTheComponentsKnowHisHtml()
    {
        $secret = uniqid();
        $component1 = new class('component1', $secret) extends AbstractComponent {

            protected $secret;

            public function __construct(string $id, string $secret)
            {
                parent::__construct($id);

                $this->secret = $secret;
            }

            public function html(): ?string
            {
                return $this->secret;
            }
        };

        $this->body->addChild($component1);
        $this->writeDocument($this->app->html());
        $this->script('window.component1 = app.getComponent("component1")');

        $this->assertContains($secret, $this->script('return component1.element.innerHTML'));
    }

    public function testTheStaticMethodExtendClassScriptFromComponentClassCanExtendTheJavaScriptClass()
    {
        $component1 = new class('component1') extends AbstractComponent {

            public static $method;

            public function html(): ?string
            {
                return null;
            }

            public static function extendClassScript(): ?string
            {
                $method = static::$method;

                return <<<JAVASCRIPT
    CClass.prototype.{$method} = function() {
        return '{$method}';
    };

JAVASCRIPT;
            }
        };

        $method = uniqid('method');
        $class1 = get_class($component1);
        $class1::$method = $method;

        $this->body->addChild($component1);
        $this->writeDocument($this->app->html());
        $this->script('window.component1 = app.getComponent("component1")');

        $this->assertEquals($method, $this->script("return component1.{$method}()"));
    }

    public function testConstructorScriptCanChangeTheJavaScriptComponentInstance()
    {
        $attribute = uniqid('attribute');
        $value = uniqid('value');

        $component = new class('component', $attribute, $value) extends AbstractComponent {

            public function __construct($id, $attribute, $value)
            {
                parent::__construct($id);

                $this->attribute = $attribute;
                $this->value = $value;
            }

            public function constructorScript(): ?string
            {
                $attribute = $this->attribute;
                $value = jsVal($this->value);

                return "component.{$attribute} = {$value};";
            }
        };

        $this->app->appendComponent('body', $component);
        $this->writeDocument($this->app->html());

        $this->assertEquals(
            $value, $this->script("return app.getComponent('component').{$attribute};")
        );
    }
}
