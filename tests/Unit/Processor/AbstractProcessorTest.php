<?php

namespace Andaniel05\GluePHP\Tests\Unit\Processor;

use PHPUnit\Framework\TestCase;
use Andaniel05\GluePHP\Processor\AbstractProcessor;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class AbstractProcessorTest extends TestCase
{
    public function testAssetReturnAnEmptyArrayByDefault()
    {
        $this->assertEquals([], AbstractProcessor::assets());
    }
}
