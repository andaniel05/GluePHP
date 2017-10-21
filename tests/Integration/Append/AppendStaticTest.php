<?php

namespace Andaniel05\GluePHP\Tests\Integration\Append;

use Andaniel05\GluePHP\Tests\StaticTestCase;

class AppendStaticTest extends StaticTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->driver->get(appUrl(__DIR__ . '/apps/app1.php'));
        $this->button1 = $this->driver->findElement(
            \WebDriverBy::cssSelector('#gphp-button1 button')
        );

        $this->button1->click(); // Act
        $this->waitForResponse();
    }

    public function testTheHtmlChildIsInsertedOnTheChildrenContainer()
    {
        $button2 = $this->driver->findElement(
            \WebDriverBy::cssSelector('#gphp-body .gphp-children #gphp-button2')
        );
        $this->assertInstanceOf(\RemoteWebElement::class, $button2);
    }

    public function testTheFrontObjectComponentIsCreatedAsChildOfTheParent()
    {
        $script = <<<JAVASCRIPT
    var button1 = app.getComponent('button1');
    var body = app.getComponent('body');
    var button2 = body.getComponent('button2');
    return button2 instanceof GluePHP.Component;
JAVASCRIPT;

        $this->assertTrue($this->script($script));
    }

    public function testTheChildComponentKnowTheApp()
    {
        $script = <<<JAVASCRIPT
    var button2 = app.getComponent('button2');
    return app === button2.app;
JAVASCRIPT;

        $this->assertTrue($this->script($script));
    }
}
