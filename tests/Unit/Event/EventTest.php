<?php

namespace Andaniel05\GluePHP\Tests\Unit\Event;

use PHPUnit\Framework\TestCase;
use Andaniel05\GluePHP\AbstractApp;
use Andaniel05\GluePHP\Event\Event;

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
