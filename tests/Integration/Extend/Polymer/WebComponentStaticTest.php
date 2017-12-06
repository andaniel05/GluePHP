<?php

namespace Andaniel05\GluePHP\Tests\Integration\Extend\Polymer;

use Andaniel05\PolyGlue\Component\WebComponent;
use Andaniel05\GluePHP\Tests\StaticTestCase;

class WebComponentStaticTest extends StaticTestCase
{
    public function testTheElementContainsBindingEvents()
    {
        $eventName = uniqid();
        $this->driver->get(appUri(
            __DIR__ . '/apps/app1.php', ['eventName' => $eventName]
        ));

        $script = <<<JAVASCRIPT
    var component = app.getComponent('component'),
        event = new Event('{$eventName}');

    component.element.dispatchEvent(event);
JAVASCRIPT;

        $this->script($script); // Act

        $this->driver->wait()->until(
            \WebDriverExpectedCondition::alertIsPresent()
        );

        $this->assertEquals(
            $eventName, $this->driver->switchTo()->alert()->getText()
        );
        $this->driver->switchTo()->alert()->accept();
    }

    public function testTheBindPropertiesAreInitializedWithBackValues()
    {
        $secret = uniqid();

        $this->driver->get(appUri(
            __DIR__ . '/apps/app2.php', ['secret' => $secret]
        ));

        $this->assertEquals($secret, $this->script("return document.querySelector('custom-element').textContent"));
    }

    public function testBindingFromGlueComponentToWebComponent()
    {
        $secret = uniqid();
        $this->driver->get(appUri(__DIR__ . '/apps/app3.php'));

        $this->script("app.getComponent('component').setText('$secret');");

        $this->assertEquals($secret, $this->script("return document.querySelector('custom-element').textContent"));
    }

    public function testBindingFromWebComponentToGlueComponent()
    {
        $secret = uniqid();
        $this->driver->get(appUri(__DIR__ . '/apps/app3.php'));

        $this->script("document.querySelector('custom-element').textContent = '{$secret}';");

        $this->assertEquals($secret, $this->script("return app.getComponent('component').model.text"));
    }
}
