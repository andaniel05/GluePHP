<?php

namespace PlatformPHP\GlueApps\Tests\Unit\Response;

use PHPUnit\Framework\TestCase;
use PlatformPHP\GlueApps\AbstractApp;
use PlatformPHP\GlueApps\Action\AbstractAction;
use PlatformPHP\GlueApps\Action\{EvalAction, RegisterAction};
use PlatformPHP\GlueApps\Response\Response;
use PlatformPHP\GlueApps\Update\{UpdateResultInterface, UpdateInterface};

class ResponseTest extends TestCase
{
    public function getResponse($code = 200)
    {
        $app = $this->getMockBuilder(AbstractApp::class)
            ->setConstructorArgs([''])
            ->setMethods(['canSendActions'])
            ->getMockForAbstractClass();
        $app->setSendActions(false);

        return new Response($app, $code);
    }

    public function testGetApp_ReturnAppArgument()
    {
        $app = $this->getMockForAbstractClass(AbstractApp::class, ['']);
        $response = new Response($app);

        $this->assertSame($app, $response->getApp());
    }

    public function testResponseCanSendActionsWhenAppCanSendActions()
    {
        $app = $this->getMockBuilder(AbstractApp::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $response = new Response($app);

        $this->assertTrue($response->canSendActions());
    }

    public function testResponseCanNotSendActionsWhenAppCanNotSendActions()
    {
        $app = $this->getMockBuilder(AbstractApp::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $app->setSendActions(false);

        $response = new Response($app);

        $this->assertFalse($response->canSendActions());
    }

    public function testGetCode_ReturnCodeArgument()
    {
        $code = rand(0, 200);
        $response = $this->getResponse($code);

        $this->assertEquals($code, $response->getCode());
    }

    public function testgetUpdateResults_ReturnAnEmptyArrayByDefault()
    {
        $response = $this->getResponse();

        $this->assertEquals([], $response->getUpdateResults());
    }

    public function testGetUpdateResults_ReturnAnArrayWithAllInsertedResults()
    {
        $result1 = $this->createMock(UpdateResultInterface::class);
        $result1->method('getId')->willReturn('result1');

        $result2 = $this->createMock(UpdateResultInterface::class);
        $result2->method('getId')->willReturn('result2');

        $response = $this->getResponse();
        $response->addUpdateResult($result1);
        $response->addUpdateResult($result2);

        $results = $response->getUpdateResults();

        $this->assertSame($result1, $results['result1']);
        $this->assertSame($result2, $results['result2']);
    }

    public function testGetClientUpdates_ReturnAnEmptyArrayByDefault()
    {
        $response = $this->getResponse();

        $this->assertEquals([], $response->getClientUpdates());
    }

    public function testGetClientUpdates_ReturnAllInsertedUpdates()
    {
        $update1 = $this->createMock(UpdateInterface::class);
        $update1->method('getId')->willReturn('update1');

        $update2 = $this->createMock(UpdateInterface::class);
        $update2->method('getId')->willReturn('update2');

        $response = $this->getResponse();

        $response->addClientUpdate($update1);
        $response->addClientUpdate($update2);

        $clientUpdates = $response->getClientUpdates();

        $this->assertSame($update1, $clientUpdates['update1']);
        $this->assertSame($update2, $clientUpdates['update2']);
    }

    public function testGetActions_ReturnAnEmptyArrayByDefault()
    {
        $response = $this->getResponse();

        $this->assertEquals([], $response->getActions());
    }

    public function testGetActions_ReturnAllTheInsertedActions()
    {
        $response = $this->getResponse();
        $action1 = new DummyAction([], 'action1');
        $response->addAction($action1);

        $actions = $response->getActions();

        $this->assertSame($action1, $actions['action1']);
    }

    public function testToJSON_Case1()
    {
        $app = $this->createMock(AbstractApp::class);
        $app->method('getToken')->willReturn('app1');
        $response = new Response($app);

        $array = json_decode($response->toJSON(), true);

        $this->assertEquals('app1', $array['appToken']);
        $this->assertEquals(200, $array['code']);
        $this->assertEquals([], $array['updateResults']);
        $this->assertEquals([], $array['clientUpdates']);
        $this->assertEquals([], $array['actions']);
    }

    public function testToJSON_Case2()
    {
        $update1 = $this->createMock(UpdateInterface::class);
        $update1->method('getId')->willReturn('update1');

        $errors1 = ['method1' => 'Message 1'];
        $updateResult1 = $this->createMock(UpdateResultInterface::class);
        $updateResult1->method('getId')->willReturn('updateResult1');
        $updateResult1->method('getUpdate')->willReturn($update1);
        $updateResult1->method('getErrors')->willReturn($errors1);

        $data1 = ['attr1' => 'value1'];
        $clientUpdate1 = $this->createMock(UpdateInterface::class);
        $clientUpdate1->method('getId')->willReturn('clientUpdate1');
        $clientUpdate1->method('getComponentId')->willReturn('component1');
        $clientUpdate1->method('getData')->willReturn($data1);

        $dataAction1 = ['data1' => 'value1'];
        $action1 = new EvalAction($dataAction1, 'action1');

        $handler = uniqid();
        $app = $this->getMockBuilder(AbstractApp::class)
            ->setConstructorArgs([''])
            ->setMethods(['getToken', 'getActionHandler'])
            ->getMockForAbstractClass();
        $app->method('getToken')->willReturn('app1');
        $app->method('getActionHandler')->willReturn($handler);
        $app->setSendActions(false);

        $response = new Response($app);
        $response->addUpdateResult($updateResult1);
        $response->addClientUpdate($clientUpdate1);
        $response->addAction($action1);

        // Act
        $array = json_decode($response->toJSON(), true);

        $expectedUpdateResults = [
            'updateResult1' => [
                'id'       => 'updateResult1',
                'updateId' => 'update1',
                'errors'   => $errors1,
            ],
        ];

        $expectedClientUpdates = [
            'clientUpdate1' => [
                'id'          => 'clientUpdate1',
                'componentId' => 'component1',
                'data'        => $data1,
            ],
        ];

        $expectedActions = [
            'action1' => [
                'id'      => 'action1',
                'data'    => $dataAction1,
                'handler' => $handler,
            ],
        ];

        $this->assertEquals('app1', $array['appToken']);
        $this->assertEquals(200, $array['code']);
        $this->assertEquals($expectedUpdateResults, $array['updateResults']);
        $this->assertEquals($expectedClientUpdates, $array['clientUpdates']);
        $this->assertEquals($expectedActions, $array['actions']);
    }

    public function testCanSendActions_ReturnInsertedValueBySetSendActions()
    {
        $response = $this->getResponse();

        $response->setSendActions(false);

        $this->assertFalse($response->canSendActions());
    }

    public function testAddAction_DoNotSendTheActionIfCanSendActionsReturnFalse()
    {
        $response = $this->getResponse();
        $action = new class([]) extends AbstractAction {

            public static function handlerScript(): string
            {
                return '';
            }
        };

        $response->addAction($action);

        $this->expectOutputString('');
    }

    public function testAddAction_AddAnRegisterActionIfActionClassIsUnknowByTheApp()
    {
        $action = new DummyAction([]);
        $response = $this->getResponse();
        $response->getApp()->setBooted(true);

        $response->addAction($action);

        $actions = array_values($response->getActions());
        $action0 = $actions[0];
        $this->assertInstanceOf(RegisterAction::class, $action0);
        $this->assertSame($action, $actions[1]);
    }
}
