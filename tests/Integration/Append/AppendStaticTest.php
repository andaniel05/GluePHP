<?php

namespace Andaniel05\GluePHP\Tests\Integration\Append;

use Andaniel05\GluePHP\Tests\StaticTestCase;

class AppendStaticTest extends StaticTestCase
{
    public function clickButton($app)
    {
        $this->driver->get(appUrl($app));
        $this->button1 = $this->driver->findElement(
            \WebDriverBy::cssSelector('#gphp-button1 button')
        );

        $this->button1->click(); // Act
    }

    public function testTheHtmlChildIsInsertedOnTheChildrenContainer()
    {
        $this->clickButton(__DIR__ . '/apps/app1.php');
        $this->waitForResponse();

        $button2 = $this->driver->findElement(
            \WebDriverBy::cssSelector('#gphp-body .gphp-children #gphp-button2')
        );
        $this->assertInstanceOf(\RemoteWebElement::class, $button2);
    }

    public function testTheFrontObjectComponentIsCreatedAsChildOfTheParent()
    {
        $this->clickButton(__DIR__ . '/apps/app1.php');
        $this->waitForResponse();

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
        $this->clickButton(__DIR__ . '/apps/app1.php');
        $this->waitForResponse();

        $script = <<<JAVASCRIPT
    var button2 = app.getComponent('button2');
    return app === button2.app;
JAVASCRIPT;

        $this->assertTrue($this->script($script));
    }

    public function testTheComponentModelContainsTheValues()
    {
        $this->clickButton(__DIR__ . '/apps/app2.php');
        $this->waitForResponse();

        $this->script("input = app.getComponent('input')");

        $this->assertEquals('secret', $this->script("return input.model.text"));
    }

    public function testTheComponentContainsHisHtmlElement()
    {
        $this->clickButton(__DIR__ . '/apps/app2.php');
        $this->waitForResponse();

        $this->script("input = app.getComponent('input')");

        $classes = $this->script("return input.element.getAttribute('class')");

        $this->assertContains('gphp-component', $classes);
        $this->assertContains('gphp-input', $classes);
    }

    public function testTheComponentsAreProcessing()
    {
        $this->clickButton(__DIR__ . '/apps/app3.php');
        $this->waitForResponse();

        $button2 = $this->driver->findElement(
            \WebDriverBy::cssSelector('#gphp-button2 button')
        );
        $button2->click(); // Act
        $this->waitForResponse();

        $this->assertEquals(
            'button2.click', $this->driver->switchTo()->alert()->getText()
        );
        $this->driver->switchTo()->alert()->accept();
    }
}
