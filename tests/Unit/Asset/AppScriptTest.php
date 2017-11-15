<?php

namespace Andaniel05\GluePHP\Tests\Unit\Asset;

use PHPUnit\Framework\TestCase;
use Andaniel05\GluePHP\AbstractApp;
use Andaniel05\GluePHP\Asset\AppScript;

class AppScriptTest extends TestCase
{
    public function testInvokeToUpdateComponentClassesOnApp()
    {
        $app = $this->getMockBuilder(AbstractApp::class)
            ->setConstructorArgs([''])
            ->setMethods(['updateComponentClasses'])
            ->getMockForAbstractClass();
        $app->expects($this->once())
            ->method('updateComponentClasses');

        $script = new AppScript('script', $app);
        $script->html();
    }

    public function testInvokeToUpdateProcessorClassesOnApp()
    {
        $app = $this->getMockBuilder(AbstractApp::class)
            ->setConstructorArgs([''])
            ->setMethods(['updateProcessorClasses'])
            ->getMockForAbstractClass();
        $app->expects($this->once())
            ->method('updateProcessorClasses');

        $script = new AppScript('script', $app);
        $script->html();
    }
}
