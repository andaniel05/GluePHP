<?php

namespace Andaniel05\GluePHP\Tests\Unit\Processor;

use PHPUnit\Framework\TestCase;
use Andaniel05\GluePHP\Processor\VueProcessor;
use Andaniel05\ComposedViews\Asset\ScriptAsset;

class VueProcessorTest extends TestCase
{
    public function testDependsOfVueScript()
    {
        $vuejs = VueProcessor::assets()['vuejs'];

        $this->assertEquals('vuejs', $vuejs->getId());
        $this->assertInstanceOf(ScriptAsset::class, $vuejs);
    }
}
