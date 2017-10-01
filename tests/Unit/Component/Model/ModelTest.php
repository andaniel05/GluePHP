<?php

namespace Andaniel05\GluePHP\Tests\Unit\Component\Model;

use PHPUnit\Framework\TestCase;
use Andaniel05\GluePHP\Component\Model\Model;
use Andaniel05\GluePHP\Tests\Unit\Component\DummyComponent1;

class ModelTest extends TestCase
{
    /**
     * @expectedException Andaniel05\GluePHP\Component\Model\Exception\ClassNotFoundException
     */
    public function testConstructorThrowClassNotFoundExceptionWhenClassNotExists()
    {
        $model = new Model(NonExistentClass::class);
    }

    /**
     * @expectedException Andaniel05\GluePHP\Component\Model\Exception\InvalidComponentClassException
     */
    public function testConstructorThrowInvalidComponentClassExceptionWhenClassIsNotInstanceOfAbstractComponent()
    {
        $model = new Model(\stdClass::class);
    }

    public function testToArray_ReturnAnArrayResultOfModelParser()
    {
        $expected = [
            'attr1' => [
                'getter' => 'getAttr1',
                'setter' => 'setAttr1',
            ],
            'attr2' => [
                'getter' => 'getAttr2',
                'setter' => 'setAttr2',
            ],
            'attr3' => [
                'getter' => 'getMyAttr3',
                'setter' => 'setMyAttr3',
            ],
        ];

        $model = new Model(DummyComponent1::class);

        $this->assertEquals($expected, $model->toArray());
    }

    public function testGetClass_ReturnTheClassArgument()
    {
        $model = new Model(DummyComponent1::class);

        $this->assertEquals(DummyComponent1::class, $model->getClass());
    }

    public function testgetAttributeList_ReturnAnArrayWithTheNamesOfTheAttributes()
    {
        $model = new Model(DummyComponent1::class);

        $this->assertEquals(
            ['attr1', 'attr2', 'attr3'],
            $model->getAttributeList()
        );
    }

    public function providerGetGetter_Cases()
    {
        return [
            [null, 'attr5'],
            ['getAttr1', 'attr1'],
            ['getAttr2', 'attr2'],
            ['getMyAttr3', 'attr3'],
        ];
    }

    /**
     * @dataProvider providerGetGetter_Cases
     */
    public function testGetGetter_Cases($expected, $attr)
    {
        $model = new Model(DummyComponent1::class);

        $this->assertEquals($expected, $model->getGetter($attr));
    }

    public function providerGetSetter_Cases()
    {
        return [
            [null, 'attr5'],
            ['setAttr1', 'attr1'],
            ['setAttr2', 'attr2'],
            ['setMyAttr3', 'attr3'],
        ];
    }

    /**
     * @dataProvider providerGetSetter_Cases
     */
    public function testSetGetter_Cases($expected, $attr)
    {
        $model = new Model(DummyComponent1::class);

        $this->assertEquals($expected, $model->getSetter($attr));
    }

    public function testGet_ReturnTheComponentModel()
    {
        $model = Model::get(DummyComponent1::class);

        $this->assertEquals(DummyComponent1::class, $model->getClass());
    }

    public function testGet_ReturnBuildTheModelOnlyOnce()
    {
        $model1 = Model::get(DummyComponent1::class);
        $model2 = Model::get(DummyComponent1::class);

        $this->assertSame($model1, $model2);
    }

    public function testSet_CanSetTheModel()
    {
        $model1 = $this->createMock(Model::class);
        $class = uniqid('ComponentClass');

        Model::set($class, $model1);

        $this->assertSame($model1, Model::get($class));
    }
}
