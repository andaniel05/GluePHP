<?php

namespace Andaniel05\GluePHP\Tests\Unit\Component;

use PHPUnit\Framework\TestCase;
use Andaniel05\GluePHP\Component\VueComponent;
use Andaniel05\GluePHP\Processor\{VueProcessor, ShortEventsProcessor};

class VueComponentTest extends TestCase
{
    public function setUp()
    {
        $this->component = new VueComponent('component');
    }

    public function testDependsOfVueProcessor()
    {
        $this->assertContains(
            VueProcessor::class, $this->component->processors()
        );
    }

    public function testDependsOfShortEventsProcessor()
    {
        $this->assertContains(
            ShortEventsProcessor::class, $this->component->processors()
        );
    }
}
