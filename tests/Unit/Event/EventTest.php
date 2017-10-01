<?php

namespace PlatformPHP\GlueApps\Tests\Unit\Event;

use PHPUnit\Framework\TestCase;
use PlatformPHP\GlueApps\AbstractApp;
use PlatformPHP\GlueApps\Event\Event;

class EventTest extends TestCase
{
    public function testArgumentGetters()
    {
        $app = $this->getMockForAbstractClass(AbstractApp::class, ['']);
        $name = uniqid();
        $data = range(0, rand(0, 10));

        $event = new Event($app, $name, $data);

        $this->assertSame($app, $event->getApp());
        $this->assertEquals($name, $event->getName());
        $this->assertEquals($data, $event->getData());
    }
}
