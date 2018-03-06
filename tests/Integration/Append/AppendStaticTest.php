<?php

namespace Andaniel05\GluePHP\Tests\Integration\Append;

use Andaniel05\GluePHP\Tests\StaticTestCase;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class AppendStaticTest extends StaticTestCase
{
    use ClickButtonTrait;

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

        $script = <<<JAVASCRIPT
    var input = app.getComponent('input');
    return input.model.text;
JAVASCRIPT;

        $this->assertEquals('secret', $this->script($script));
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

        $this->driver->wait()->until(
            \WebDriverExpectedCondition::alertIsPresent()
        );

        $this->assertEquals(
            'button2.click',
            $this->driver->switchTo()->alert()->getText()
        );
        $this->driver->switchTo()->alert()->accept();
    }

    public function testIfBeforeInsertionExistsAnyComponentWithIdEqualToNewItIsDetached()
    {
        $this->clickButton(__DIR__ . '/apps/app7.php');
        $this->waitForResponse();

        $this->assertEquals(1, $this->script("return document.querySelectorAll('#gphp-button1').length"));
    }
}
