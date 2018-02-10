<?php

namespace Andaniel05\GluePHP\Tests\Integration\Deletion;

use Andaniel05\GluePHP\Tests\StaticTestCase;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
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

    public function testDeletionAfterDynamicInsertion()
    {
        $this->driver->get(appUri(__DIR__.'/apps/app2.php'));
        $button1 = $this->driver->findElement(
            \WebDriverBy::cssSelector('#gphp-button1 button')
        );
        $button2 = $this->driver->findElement(
            \WebDriverBy::cssSelector('#gphp-button2 button')
        );

        // Prueba que al hacer clic en el botón 1 se inserta el componente input.
        //

        $button1->click();
        $this->waitForResponse();

        $script = <<<JAVASCRIPT
    var body = app.getComponent('body');
    var input = body.getComponent('input');
    return input instanceof GluePHP.Component;
JAVASCRIPT;
        $this->assertTrue($this->script($script));

        $input = $this->driver->findElement(\WebDriverBy::cssSelector('#gphp-input'));
        $this->assertInstanceOf(\RemoteWebElement::class, $input);

        // Prueba que al hacer clic en el botón 2 se elimina el componente input.
        //

        $button2->click();
        $this->waitForResponse();

        $this->assertNull($this->script("return app.getComponent('input');"));
        $this->assertNull($this->script("return document.getElementById('gphp-input');"));
    }
}
