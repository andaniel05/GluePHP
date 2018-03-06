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
            [__DIR__ . '/apps/app2.php'],
            [__DIR__ . '/apps/app3.php'],
            [__DIR__ . '/apps/app4.php'],
        ];
    }

    /**
     * @dataProvider appProvider
     */
    public function test($app)
    {
        $this->driver->get(appUri($app));
        $code = rand(97, 122);
        $letter = chr($code);

        $this->driver->findElement(\WebDriverBy::cssSelector('#gphp-input input'))
            ->sendKeys($letter);

        $this->driver->wait()->until(\WebDriverExpectedCondition::alertIsPresent());

        $text = $this->driver->switchTo()->alert()->getText();
        $this->driver->switchTo()->alert()->accept();

        $this->assertContains($letter, $text);
        $this->assertContains(strval($code), $text);
    }
}
