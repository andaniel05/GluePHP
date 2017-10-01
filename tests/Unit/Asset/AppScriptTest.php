<?php

namespace PlatformPHP\GlueApps\Tests\Unit\Asset;

use PHPUnit\Framework\TestCase;
use PlatformPHP\GlueApps\AbstractApp;
use PlatformPHP\GlueApps\Asset\AppScript;

class AppScriptTest extends TestCase
{
    public function testGetSource_InvokeToUpdateComponentClassesOnApp()
    {
        $app = $this->getMockBuilder(AbstractApp::class)
            ->setConstructorArgs([''])
            ->setMethods(['updateComponentClasses'])
            ->getMockForAbstractClass();
        $app->expects($this->once())
            ->method('updateComponentClasses');

        $script = new AppScript('script', $app);

        $script->getSource();
    }
}
