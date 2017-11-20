<?php

namespace Andaniel05\GluePHP\Tests\Functional\Processor;

use Andaniel05\GluePHP\Component\AbstractComponent;
use Andaniel05\GluePHP\Tests\StaticTestCase;

class DefaultProcessorStaticTest extends StaticTestCase
{
    public function providerGPhpEvent()
    {
        return [
            ['gphp-event'], ['data-gphp-event']
        ];
    }

    /**
     * @dataProvider providerGPhpEvent
     */
    public function testGPhpEventAttribute_BindOneEventToTheComponent($attribute)
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
     * @dataProvider providerGPhpEvent
     */
    public function testGPhpEventAttribute_BindSeveralEventsToTheComponent($attribute)
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

    public function providerGPhpBindValue()
    {
        return [
            ['gphp-bind-value'], ['data-gphp-bind-value']
        ];
    }

    /**
     * @dataProvider providerGPhpBindValue
     */
    public function testGPhpBindValue_ViewToComponent($attribute)
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
     * @dataProvider providerGPhpBindValue
     */
    public function testGPhpBindValue_ComponentToView_TheUpdateIsRegistered($attribute)
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

    public function providerGPhpBindAttr()
    {
        return [
            ['gphp-bind-attr'], ['data-gphp-bind-attr']
        ];
    }

    /**
     * @dataProvider providerGPhpBindAttr
     */
    public function testBoundsAttributesAreInitialized($gphpBindAttr)
    {
        $htmlAttrToBind = uniqid('attr');

        $component1 = new class($gphpBindAttr, $htmlAttrToBind) extends AbstractComponent {

            /**
             * @Glue
             */
            protected $gAttr;

            protected $attribute;

            public function __construct($gphpBindAttr, $htmlAttrToBind)
            {
                parent::__construct('component1');

                $this->attribute = $gphpBindAttr.'-'.$htmlAttrToBind;
            }

            public function html(): ?string
            {
                return "<div id=\"div\" {$this->attribute}=\"gAttr\"></div>";
            }
        };

        $secret = uniqid();
        $component1->setGAttr($secret);

        $this->body->addChild($component1);
        $this->writeDocument($this->app->html());

        $this->assertEquals(
            $secret, $this->script("return document.getElementById('div').getAttribute('{$htmlAttrToBind}');")
        );
    }

    /**
     * @dataProvider providerGPhpBindAttr
     */
    public function testAttributeBindingFromTheComponentModelToTheView($gphpBindAttr)
    {
        $htmlAttrToBind = uniqid('attr');

        $component1 = new class($gphpBindAttr, $htmlAttrToBind) extends AbstractComponent {

            /**
             * @Glue
             */
            protected $gAttr;

            protected $attribute;

            public function __construct($gphpBindAttr, $htmlAttrToBind)
            {
                parent::__construct('component1');

                $this->attribute = $gphpBindAttr.'-'.$htmlAttrToBind;
            }

            public function html(): ?string
            {
                return "<div id=\"div\" {$this->attribute}=\"gAttr\"></div>";
            }
        };

        $this->body->addChild($component1);
        $this->writeDocument($this->app->html());

        $secret = uniqid();
        $this->script("app.getComponent('component1').setGAttr('{$secret}')");

        $this->assertEquals(
            $secret, $this->script("return document.getElementById('div').getAttribute('{$htmlAttrToBind}');")
        );
    }

    // /**
    //  * @dataProvider providerGPhpBindAttr
    //  */
    // public function testAttributeBindingFromTheViewToTheComponentModel($gphpBindAttr)
    // {
    //     $htmlAttrToBind = uniqid('attr');

    //     $component1 = new class($gphpBindAttr, $htmlAttrToBind) extends AbstractComponent {

    //         /**
    //          * @Glue
    //          */
    //         protected $gAttr;

    //         protected $attribute;

    //         public function __construct($gphpBindAttr, $htmlAttrToBind)
    //         {
    //             parent::__construct('component1');

    //             $this->attribute = $gphpBindAttr.'-'.$htmlAttrToBind;
    //         }

    //         public function html(): ?string
    //         {
    //             return "<div id=\"div\" {$this->attribute}=\"gAttr\"></div>";
    //         }
    //     };

    //     $this->body->addChild($component1);
    //     $this->writeDocument($this->app->html());

    //     $secret = uniqid();
    //     $this->script("document.getElementById('div').setAttribute('{$htmlAttrToBind}', '{$secret}');");

    //     $this->assertEquals(
    //         $secret, $this->script("return app.getComponent('component1').model.gAttr;")
    //     );
    // }

    public function providerGPhpBindHtml()
    {
        return [
            ['gphp-bind-html'], ['data-gphp-bind-html']
        ];
    }

    /**
     * @dataProvider providerGPhpBindHtml
     */
    public function testHtmlBindingInitializeInnerHTMLOnBoundChilds($attribute)
    {
        $component1 = new class($attribute) extends AbstractComponent {

            /**
             * @Glue
             */
            protected $gAttr;

            protected $attribute;

            public function __construct($attribute)
            {
                parent::__construct('component1');

                $this->attribute = $attribute;
            }

            public function html(): ?string
            {
                return "<div id=\"div\" {$this->attribute}=\"gAttr\"></div>";
            }
        };

        $secret = uniqid();
        $component1->setGAttr($secret);

        $this->body->addChild($component1);
        $this->writeDocument($this->app->html());

        $this->assertEquals(
            $secret, $this->script("return document.getElementById('div').innerHTML;")
        );
    }

    /**
     * @dataProvider providerGPhpBindHtml
     */
    public function testHtmlBindingFromTheComponentModelToTheView($attribute)
    {
        $component1 = new class($attribute) extends AbstractComponent {

            /**
             * @Glue
             */
            protected $gAttr;

            protected $attribute;

            public function __construct($attribute)
            {
                parent::__construct('component1');

                $this->attribute = $attribute;
            }

            public function html(): ?string
            {
                return "<div id=\"div\" {$this->attribute}=\"gAttr\"></div>";
            }
        };

        $this->body->addChild($component1);
        $this->writeDocument($this->app->html());

        $secret = uniqid();
        $this->script("app.getComponent('component1').setGAttr('{$secret}')");

        $this->assertEquals(
            $secret, $this->script("return document.getElementById('div').innerHTML;")
        );
    }

    // /**
    //  * @dataProvider providerGPhpBindHtml
    //  */
    // public function testHtmlBindingFromTheViewToTheComponentModel($attribute)
    // {
    //     $component1 = new class($attribute) extends AbstractComponent {

    //         /**
    //          * @Glue
    //          */
    //         protected $gAttr;

    //         protected $attribute;

    //         public function __construct($attribute)
    //         {
    //             parent::__construct('component1');

    //             $this->attribute = $attribute;
    //         }

    //         public function html(): ?string
    //         {
    //             return "<div id=\"div\" {$this->attribute}=\"gAttr\"></div>";
    //         }
    //     };

    //     $this->body->addChild($component1);
    //     $this->writeDocument($this->app->html());

    //     $secret = uniqid();
    //     $this->script("document.getElementById('div').innerHTML = '{$secret}'");

    //     $this->assertEquals(
    //         $secret, $this->script("return app.getComponent('component1').model.gAttr;")
    //     );
    // }
}
