<?php

namespace Andaniel05\GluePHP\Tests\Functional\Processor;

use Andaniel05\GluePHP\Component\AbstractComponent;
use Andaniel05\GluePHP\Tests\StaticTestCase;

class ShortEventsProcessorStaticTest extends StaticTestCase
{
    public function testBindEventWithToken()
    {
        $secret = uniqid();
        $eventName = uniqid('event');

        $component = new class('component', $eventName) extends AbstractComponent {

            protected $eventName;

            public function __construct($componentId, $eventName)
            {
                parent::__construct($componentId);

                $this->eventName = $eventName;
            }

            public function html(): ?string
            {
                return "<button @@{$this->eventName}>Click Me!</button>";
            }
        };

        $this->body->addChild($component);

        $this->app->setControllerPath(null);
        $this->writeDocument($this->app->html());

        $script = <<<JAVASCRIPT
window.component = app.getComponent("component");
component.addListener('{$eventName}', function() {
    alert('{$secret}');
});
JAVASCRIPT;
        $this->script($script);

        // Act
        $script = <<<JAVASCRIPT
var button = document.querySelector('button'),
    event = new Event('{$eventName}');
button.dispatchEvent(event);
JAVASCRIPT;
        $this->script($script);

        $this->assertEquals(
            $secret, $this->driver->switchTo()->alert()->getText()
        );
        $this->driver->switchTo()->alert()->accept();
    }
}
