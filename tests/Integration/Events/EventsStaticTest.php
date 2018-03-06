<?php

namespace Andaniel05\GluePHP\Tests\Integration\Events;

use Andaniel05\GluePHP\Tests\StaticTestCase;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class EventsStaticTest extends StaticTestCase
{
    public function appProvider()
    {
        return [
            [__DIR__ . '/apps/app1.php'],
            // [__DIR__ . '/apps/app2.php'],
        ];
    }

    /**
     * @dataProvider appProvider
     */
    public function test($app)
    {
        $this->driver->get(appUri($app));

        $this->driver->findElement(\WebDriverBy::cssSelector('#gphp-input input'))
            ->sendKeys('a');

        $this->waitForResponse();
        $this->driver->wait()->until(\WebDriverExpectedCondition::alertIsPresent());

        $this->assertEquals('a', $this->driver->switchTo()->alert()->getText());
        $this->driver->switchTo()->alert()->accept();
    }
}
