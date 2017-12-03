<?php

namespace Andaniel05\GluePHP\Tests\Functional\Processor;

use Andaniel05\GluePHP\Component\AbstractComponent;
use Andaniel05\GluePHP\Tests\StaticTestCase;

class BindEventsProcessorStaticTest extends StaticTestCase
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
}
