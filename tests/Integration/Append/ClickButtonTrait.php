<?php

namespace Andaniel05\GluePHP\Tests\Integration\Append;

trait ClickButtonTrait
{
    public function clickButton($app)
    {
        $this->driver->get(appUri($app));
        $this->button1 = $this->driver->findElement(
            \WebDriverBy::cssSelector('#gphp-button1 button')
        );

        $this->button1->click(); // Act
    }
}
