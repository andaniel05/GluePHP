<?php

namespace Andaniel05\GluePHP\Tests\Unit\Extend\VueJS;

use PHPUnit\Framework\TestCase;
use Andaniel05\GluePHP\Extend\VueJS\VueProcessor;
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
