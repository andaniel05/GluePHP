<?php

namespace PlatformPHP\GlueApps\Tests\Integration\DynamicRegistration;

use PlatformPHP\GlueApps\Tests\StaticTestCase;

class DynamicRegistrationStaticTest extends StaticTestCase
{
    public function clickButton($app)
    {
        $this->driver->get(appUrl($app));

        $this->button = $this->driver->findElement(
            \WebDriverBy::cssSelector('#cv-button button')
        );
        $this->button->click(); // Act
        $this->waitForResponse();
    }

    public function testRegisterActionClass()
    {
        $this->clickButton(__DIR__ . '/apps/app1.php');

        $secret = uniqid();
        $this->script("app.actionHandlers['alert']('$secret')");
        $this->assertEquals($secret, $this->driver->switchTo()->alert()->getText());
        $this->driver->switchTo()->alert()->accept();
    }

    public function testRegisterComponentClass()
    {
        $this->clickButton(__DIR__ . '/apps/app2.php');

        $id = uniqid();
        $component = $this->script("return new app.componentClasses.input('$id')");

        $this->assertEquals($id, $component['id']);
    }

    public function testTheUnknowActionsAreRegisteringDynamically()
    {
        $this->clickButton(__DIR__ . '/apps/app3.php');

        $this->assertEquals('secret', $this->driver->switchTo()->alert()->getText());
        $this->driver->switchTo()->alert()->accept();
    }
}
