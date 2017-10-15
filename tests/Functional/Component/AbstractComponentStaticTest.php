<?php

namespace Andaniel05\GluePHP\Tests\Functional\Component;

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

    public function providerGEventAttribute()
    {
        return [
            ['g-event'], ['data-g-event']
        ];
    }

    /**
     * @dataProvider providerGEventAttribute
     */
    public function testGEventAttribute_BindOneEventToTheComponent($attribute)
    {
        $secret = uniqid();
        $componentId = uniqid('component');
        $component = new class($componentId, $attribute) extends AbstractComponent {

            protected $attribute;

            public function __construct($componentId, $attribute)
            {
                parent::__construct($componentId);

                $this->attribute = $attribute;
            }

            public function html(): ?string
            {
                return "<button id=\"button\" {$this->attribute}=\"click\">Click Me!</button>";
            }
        };

        $this->body->addChild($component);

        $this->app->setControllerPath(null);
        $this->writeDocument($this->app->html());

        $script = <<<JAVASCRIPT
window.{$componentId} = app.getComponent("{$componentId}");
{$componentId}.addListener('click', function() {
    alert('{$secret}');
});
JAVASCRIPT;

        $this->script($script);
        $this->driver->findElement(\WebDriverBy::id('button'))->click();

        $this->assertEquals(
            $secret, $this->driver->switchTo()->alert()->getText()
        );
        $this->driver->switchTo()->alert()->accept();
    }

    /**
     * @dataProvider providerGEventAttribute
     */
    public function testGEventAttribute_BindSeveralEventsToTheComponent($attribute)
    {
        $componentId = uniqid('component');
        $customEvent1 = uniqid('customEvent1');
        $customEvent2 = uniqid('customEvent2');

        $component = new class($componentId, $attribute, $customEvent1, $customEvent2) extends AbstractComponent {

            protected $attribute;
            protected $customEvent1;
            protected $customEvent2;

            public function __construct($componentId, $attribute, $customEvent1, $customEvent2)
            {
                parent::__construct($componentId);

                $this->attribute = $attribute;
                $this->customEvent1 = $customEvent1;
                $this->customEvent2 = $customEvent2;
            }

            public function html(): ?string
            {
                return "<button id=\"button\" {$this->attribute}=\"{$this->customEvent1} {$this->customEvent2}\">Click Me!</button>";
            }
        };

        $this->body->addChild($component);

        $this->app->setControllerPath(null);
        $this->writeDocument($this->app->html());

        $secret = uniqid();

        $script = <<<JAVASCRIPT
window.{$componentId} = app.getComponent("{$componentId}");
window.button = document.getElementById('button');

var alertSecret = function() {
    alert('{$secret}');
};

{$componentId}.addListener('{$customEvent1}', alertSecret);
{$componentId}.addListener('{$customEvent2}', alertSecret);

JAVASCRIPT;
        $this->script($script);

        // Custom Event 1
        //

        $script = <<<JAVASCRIPT
var event = new Event('{$customEvent1}');
button.dispatchEvent(event);
JAVASCRIPT;

        $this->script($script);
        $this->assertEquals(
            $secret, $this->driver->switchTo()->alert()->getText()
        );
        $this->driver->switchTo()->alert()->accept();

        // Custom Event 2
        //

        $script = <<<JAVASCRIPT
var event = new Event('{$customEvent2}');
button.dispatchEvent(event);
JAVASCRIPT;

        $this->script($script);
        $this->assertEquals(
            $secret, $this->driver->switchTo()->alert()->getText()
        );
        $this->driver->switchTo()->alert()->accept();
    }

    public function providerGBindAttribute()
    {
        return [
            ['g-bind'], ['data-g-bind']
        ];
    }

    /**
     * @dataProvider providerGBindAttribute
     */
    public function testGBindAttribute_ViewToComponent($attribute)
    {
        $component1 = new class($attribute) extends AbstractComponent {

            /**
             * @Glue
             */
            protected $text;

            protected $attribute;

            public function __construct(string $attribute)
            {
                parent::__construct('component1');

                $this->attribute = $attribute;
            }

            public function html(): ?string
            {
                return "<input type=\"text\" {$this->attribute}=\"text\">";
            }
        };

        $this->body->addChild($component1);
        $this->writeDocument($this->app->html());
        $this->script('window.component1 = app.getComponent("component1")');

        $secret = uniqid();
        $script = <<<JAVASCRIPT
var input = document.querySelector('input');
input.value = '{$secret}';
var event = new Event('change');
input.dispatchEvent(event);

JAVASCRIPT;
        $this->script($script);

        $this->assertEquals(
            $secret,
            $this->script("return component1.model.text")
        );
    }

    /**
     * @dataProvider providerGBindAttribute
     */
    public function testGBindAttribute_ComponentToView_TheUpdateIsRegistered($attribute)
    {
        $component1 = new class($attribute) extends AbstractComponent {

            /**
             * @Glue
             */
            protected $text;

            protected $attribute;

            public function __construct(string $attribute)
            {
                parent::__construct('component1');

                $this->attribute = $attribute;
            }

            public function html(): ?string
            {
                return "<input type=\"text\" {$this->attribute}=\"text\">";
            }
        };

        $this->body->addChild($component1);
        $this->writeDocument($this->app->html());
        $this->script('window.component1 = app.getComponent("component1")');

        $secret = uniqid();
        $this->script("component1.setText('{$secret}')");

        $this->assertEquals(
            $secret,
            $this->script("return document.querySelector('input').value")
        );

        $this->assertEquals(
            $secret,
            $this->script("return app.buffer.component1.text")
        );
    }
}
