<?php

namespace Andaniel05\GluePHP\Tests\Unit\Action;

use PHPUnit\Framework\TestCase;
use Andaniel05\GluePHP\Action\AbstractAction;

class AbstractActionTest extends TestCase
{
    public function testArgumentGetters()
    {
        $data = range(0, rand(0, 10));
        $id = uniqid();

        $action = $this->getMockBuilder(AbstractAction::class)
            ->setConstructorArgs([$data, $id])
            ->getMockForAbstractClass();

        $this->assertEquals($data, $action->getData());
        $this->assertEquals($id, $action->getId());
    }

    public function testIdStartWithActionWord()
    {
        $action = new DummyAction1([]);

        $this->assertStringStartsWith('dummyaction1', $action->getId());
    }

    public function testIsSent_ReturnFalseByDefault()
    {
        $action = $this->getMockBuilder(AbstractAction::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->assertFalse($action->isSent());
    }
}
