<?php

namespace Andaniel05\GluePHP\Tests\Integration\Deletion;

use Andaniel05\GluePHP\Tests\StaticTestCase;

class DeletionStaticTest extends StaticTestCase
{
    public function clickButton($app)
    {
        $this->driver->get(appUri($app));
        $this->button1 = $this->driver->findElement(
            \WebDriverBy::cssSelector('#gphp-button1 button')
        );

        $this->button1->click(); // Act
    }

    public function appProvider()
    {
        return [
            [__DIR__.'/apps/app1.php'],
        ];
    }

    /**
     * @dataProvider appProvider
     */
    public function testTheJavaScriptComponentObjectIsRemoved($app)
    {
        $this->clickButton($app);
        $this->waitForResponse();

        $this->assertTrue($this->script("return null === app.getComponent('button2');"));
    }

    /**
     * @dataProvider appProvider
     */
    public function testTheHtmlElementIsDeleted($app)
    {
        $this->clickButton($app);
        $this->waitForResponse();

        $this->assertNull($this->script("return document.getElementById('gphp-button2')"));
    }
}
