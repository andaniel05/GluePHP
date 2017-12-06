<?php

namespace Andaniel05\GluePHP\Tests\Integration\Updates;

use Andaniel05\GluePHP\Tests\StaticTestCase;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class UpdatesStaticTest extends StaticTestCase
{
    public function loadComponents()
    {
        $this->button = $this->driver->findElement(
            \WebDriverBy::cssSelector('#gphp-button button')
        );
        $this->input1 = $this->driver->findElement(
            \WebDriverBy::cssSelector('#gphp-input1 input')
        );
        $this->input2 = $this->driver->findElement(
            \WebDriverBy::cssSelector('#gphp-input2 input')
        );
    }

    public function appProvider()
    {
        return [
            [__DIR__ . '/apps/app1.php'],
            [__DIR__ . '/apps/app2.php'],
        ];
    }

    /**
     * @dataProvider appProvider
     */
    public function test($app)
    {
        $this->driver->get(appUri($app));
        $this->loadComponents();

        for ($i = 1; $i <= 2; $i++) {
            $value = uniqid();
            $this->input1->clear();
            $this->input1->sendKeys($value);
            $this->button->click(); // Act
            $this->waitForResponse();

            $this->assertEquals($value, $this->input2->getAttribute('value'));
        }
    }
}
