<?php

namespace Andaniel05\GluePHP\Tests\Integration\Append;

use Andaniel05\GluePHP\Tests\StaticTestCase;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class NestedChildrenStaticTest extends StaticTestCase
{
    use ClickButtonTrait;

    public function testTheHtmlChildIsInsertedOnTheChildrenContainer()
    {
        $this->clickButton(__DIR__ . '/apps/app4.php');
        $this->waitForResponse();

        $button2 = $this->driver->findElement(
            \WebDriverBy::cssSelector(
                '.gphp-body-children .gphp-sidebar-children #gphp-button2'
            )
        );
        $this->assertInstanceOf(\RemoteWebElement::class, $button2);
    }

    public function testTheNestedComponentIsRenderedOnlyOnce()
    {
        $this->clickButton(__DIR__ . '/apps/app4.php');
        $this->waitForResponse();

        $this->assertEquals(
            1,
            $this->script('return document.querySelectorAll("#gphp-sidebar").length')
        );
        $this->assertEquals(
            1,
            $this->script('return document.querySelectorAll("#gphp-button2").length')
        );
    }

    public function testTheFrontObjectComponentIsCreatedAsChildOfTheParent()
    {
        $this->clickButton(__DIR__ . '/apps/app4.php');
        $this->waitForResponse();

        $script = <<<JAVASCRIPT
    var sidebar = app.getComponent('sidebar');
    var button2 = sidebar.getComponent('button2');
    return button2 instanceof GluePHP.Component;
JAVASCRIPT;

        $this->assertTrue($this->script($script));
    }

    public function testTheChildComponentKnowTheApp()
    {
        $this->clickButton(__DIR__ . '/apps/app4.php');
        $this->waitForResponse();

        $script = <<<JAVASCRIPT
    var button2 = app.getComponent('button2');
    return app === button2.app;
JAVASCRIPT;

        $this->assertTrue($this->script($script));
    }

    public function testTheComponentModelContainsTheValues()
    {
        $this->clickButton(__DIR__ . '/apps/app5.php');
        $this->waitForResponse();

        $script = <<<JAVASCRIPT
    var input = app.getComponent('input');
    return input.model.text;
JAVASCRIPT;

        $this->assertEquals('secret', $this->script($script));
    }

    public function testTheComponentContainsHisHtmlElement()
    {
        $this->clickButton(__DIR__ . '/apps/app5.php');
        $this->waitForResponse();

        $this->script("input = app.getComponent('input')");

        $classes = $this->script("return input.element.getAttribute('class')");

        $this->assertContains('gphp-component', $classes);
        $this->assertContains('gphp-input', $classes);
    }

    public function testTheComponentsAreProcessing()
    {
        $this->clickButton(__DIR__ . '/apps/app6.php');
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
}
