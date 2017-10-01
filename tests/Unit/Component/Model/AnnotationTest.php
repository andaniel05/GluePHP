<?php

namespace PlatformPHP\GlueApps\Tests\Unit\Component\Model;

use PHPUnit\Framework\TestCase;
use PlatformPHP\GlueApps\Component\Model\Annotation;

class AnnotationTest extends TestCase
{
    public function testArgumentGetters()
    {
        $name = uniqid();
        $atts = range(0, range(0, 10));

        $annotation = new Annotation($name, $atts);

        $this->assertEquals($name, $annotation->getName());
        $this->assertEquals($atts, $annotation->getAttributes());
    }

    public function testGetAttribute_ReturnValueOfAttribute()
    {
        $atts = [
            'attr1' => 'value1',
            'attr2' => 'value2',
        ];

        $annotation = new Annotation('annotation1', $atts);

        $this->assertEquals('value1', $annotation->getAttribute('attr1'));
        $this->assertEquals('value2', $annotation->getAttribute('attr2'));
        $this->assertNull($annotation->getAttribute('attr3'));
    }

    public function testParseString_Cases()
    {
        $str = <<<'STR'
/**
 * @Annotation1
 * @Annotation2(attr1="value1", attr2="value2")
 * @Annotation3 (   attr1="value1",attr2="value2", )
 * @Annotation4()
 */
STR;

        $annotations = Annotation::parseString($str);
        $annotation1 = $annotations[0];
        $annotation2 = $annotations[1];
        $annotation3 = $annotations[2];
        $annotation4 = $annotations[3];

        $this->assertEquals('Annotation1', $annotation1->getName());
        $this->assertEquals([], $annotation1->getAttributes());

        $this->assertEquals('Annotation2', $annotation2->getName());
        $this->assertEquals(
            ['attr1' => 'value1', 'attr2' => 'value2'],
            $annotation2->getAttributes()
        );

        $this->assertEquals('Annotation3', $annotation3->getName());
        $this->assertEquals(
            ['attr1' => 'value1', 'attr2' => 'value2'],
            $annotation3->getAttributes()
        );

        $this->assertEquals('Annotation4', $annotation4->getName());
        $this->assertEquals([], $annotation4->getAttributes());
    }
}
