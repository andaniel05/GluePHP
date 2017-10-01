<?php

namespace PlatformPHP\GlueApps\Tests\Unit\Request;

use PHPUnit\Framework\TestCase;
use PlatformPHP\GlueApps\Request\Request;
use PlatformPHP\GlueApps\Update\UpdateInterface;

class RequestTest extends TestCase
{
    public function testArgumentGetters()
    {
        $app = uniqid();
        $status = uniqid();
        $event = uniqid();

        $request = new Request($app, $status, $event);

        $this->assertEquals($app, $request->getAppToken());
        $this->assertEquals($status, $request->getStatus());
        $this->assertEquals($event, $request->getEventName());
    }

    public function testGetServerUpdates_ReturnAnEmptyArrayByDefault()
    {
        $request = new Request('app1', 'status1', 'event1');

        $this->assertEquals([], $request->getServerUpdates());
    }

    public function testGetServerUpdates_ReturnAllUpdates()
    {
        $update1 = $this->createMock(UpdateInterface::class);
        $update1->method('getId')->willReturn('update1');

        $update2 = $this->createMock(UpdateInterface::class);
        $update2->method('getId')->willReturn('update2');

        $request = new Request('app1', 'status1', 'event1');
        $request->addServerUpdate($update1);
        $request->addServerUpdate($update2);

        $updates = $request->getServerUpdates();

        $this->assertSame($update1, $updates['update1']);
        $this->assertSame($update2, $updates['update2']);
    }

    public function testCreateFromJSON_ReturnNullWhenJsonDataIsNotValid()
    {
        $this->assertNull(Request::createFromJSON(''));
    }

    public function testCreateFromJSON_Case1()
    {
        $array = [
            'appToken'      => 'app1',
            'status'        => null,
            'eventName'     => 'event1',
            'eventData'     => [],
            'serverUpdates' => [],
        ];

        $request = Request::createFromJSON(json_encode($array));

        $this->assertEquals('app1', $request->getAppToken());
        $this->assertEquals(null, $request->getStatus());
        $this->assertEquals('event1', $request->getEventName());
        $this->assertEquals([], $request->getEventData());
        $this->assertEquals([], $request->getServerUpdates());
    }

    public function testCreateFromJSON_Case2()
    {
        $array = [
            'appToken'      => 'app2',
            'status'        => 'status2',
            'eventName'     => 'event2',
            'eventData'     => ['data1' => 'value1'],
            'serverUpdates' => [
                [
                    'id'          => 'update1',
                    'componentId' => 'component1',
                    'data'        => ['data1' => 'value1']
                ],
            ],
        ];

        $request = Request::createFromJSON(json_encode($array));
        $update1 = $request->getServerUpdates()['update1'];

        $this->assertEquals('app2', $request->getAppToken());
        $this->assertEquals('status2', $request->getStatus());
        $this->assertEquals('event2', $request->getEventName());
        $this->assertEquals(['data1' => 'value1'], $request->getEventData());

        $this->assertEquals('update1', $update1->getId());
        $this->assertEquals('component1', $update1->getComponentId());
        $this->assertEquals(['data1' => 'value1'], $update1->getData());
    }

    public function testGetEventData_ReturnAnEmptyArrayByDefault()
    {
        $request = new Request('app1', 'status1', 'event1');

        $this->assertEquals([], $request->getEventData());
    }

    public function testGetEventData_ReturnTheEventDataArgument()
    {
        $eventData = ['data1' => 'value1'];

        $request = new Request('app1', 'status1', 'event1', $eventData);

        $this->assertEquals($eventData, $request->getEventData());
    }
}
