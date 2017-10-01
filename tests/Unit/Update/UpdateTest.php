<?php

namespace PlatformPHP\GlueApps\Tests\Unit\Update;

use PHPUnit\Framework\TestCase;
use PlatformPHP\GlueApps\Update\Update;

class UpdateTest extends TestCase
{
    public function testArgumentGetters()
    {
        $componentId = uniqid();
        $data = range(0, rand(0, 10));
        $id = uniqid();

        $update = new Update($componentId, $data, $id);

        $this->assertEquals($componentId, $update->getComponentId());
        $this->assertEquals($data, $update->getData());
        $this->assertEquals($id, $update->getId());
    }

    public function testIdStartWithUp_WhenIdArgumentIsNull()
    {
        $update = new Update('component1', []);

        $this->assertStringStartsWith('up_', $update->getId());
    }
}
