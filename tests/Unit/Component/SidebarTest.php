<?php

namespace Andaniel05\GluePHP\Component;

use PHPUnit\Framework\TestCase;
use Andaniel05\GluePHP\Component\Sidebar;
use Andaniel05\ComposedViews\Component\SidebarInterface;

class SidebarTest extends TestCase
{
    public function setUp()
    {
        $this->sidebar = new Sidebar;
    }

    public function testProcessors_ReturnAnEmptyArray()
    {
        $this->assertEquals([], $this->sidebar->processors());
    }

    public function testIsInstanceOfComposedViewSidebarInterface()
    {
        $this->assertInstanceOf(SidebarInterface::class, $this->sidebar);
    }
}
