<?php

namespace Andaniel05\GluePHP\Tests\Integration\Append;

use Andaniel05\GluePHP\Tests\StaticTestCase;

class AppendStaticTest extends StaticTestCase
{
    public function testTheHtmlChildIsInsertedOnTheChildrenContainer()
    {
        $this->driver->get(appUrl(__DIR__ . '/apps/app1.php'));
        $button1 = $this->driver->findElement(
            \WebDriverBy::cssSelector('#gphp-button1 button')
        );

        $button1->click(); // Act
        $this->waitForResponse();

        $button2 = $this->driver->findElement(
            \WebDriverBy::cssSelector('#gphp-body .gphp-children #gphp-button2')
        );
        $this->assertInstanceOf(\RemoteWebElement::class, $button2);
    }
}
