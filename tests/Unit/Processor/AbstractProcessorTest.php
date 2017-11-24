<?php

namespace Andaniel05\GluePHP\Tests\Unit\Processor;

use PHPUnit\Framework\TestCase;
use Andaniel05\GluePHP\Processor\AbstractProcessor;

class AbstractProcessorTest extends TestCase
{
    public function testAssetReturnAnEmptyArrayByDefault()
    {
        $this->assertEquals([], AbstractProcessor::assets());
    }
}
