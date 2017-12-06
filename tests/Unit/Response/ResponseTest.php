<?php

namespace Andaniel05\GluePHP\Tests\Unit\Response;

use PHPUnit\Framework\TestCase;
use Andaniel05\GluePHP\AbstractApp;
use Andaniel05\GluePHP\Action\AbstractAction;
use Andaniel05\GluePHP\Action\EvalAction;
use Andaniel05\GluePHP\Action\RegisterAction;
use Andaniel05\GluePHP\Request\RequestInterface;
use Andaniel05\GluePHP\Response\Response;
use Andaniel05\GluePHP\Update\UpdateResultInterface;
use Andaniel05\GluePHP\Update\UpdateInterface;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class ResponseTest extends TestCase
{
    public function getApp()
    {
        $app = $this->getMockBuilder(AbstractApp::class)
            ->setConstructorArgs([''])
            ->setMethods(['canSendActions'])
            ->getMockForAbstractClass();
        $app->setSendActions(false);

        return $app;
    }

    public function getResponse($code = 200)
    {
        return new Response($this->getApp(), $code);
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
        $response = new Response($app);

        $array = json_decode($response->toJSON(), true);

        $this->assertEquals(200, $array['code']);
        $this->assertEquals([], $array['updateResults']);
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

        $expectedActions = [
            'action1' => [
                'id'      => 'action1',
                'data'    => $dataAction1,
                'handler' => $handler,
            ],
        ];

        $this->assertEquals(200, $array['code']);
        $this->assertEquals($expectedUpdateResults, $array['updateResults']);
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
        $executed = false;
        $eventName = uniqid();

        $app = $this->getApp();
        $app->setBooted(true);

        $request = $this->createMock(RequestInterface::class);
        $request->method('getAppToken')->willReturn($app->getToken());
        $request->method('getEventName')->willReturn($eventName);

        $app->on($eventName, function () use (&$executed, $app) {
            $executed = true;
            $action = new DummyAction([]);

            $response = $app->getResponse();
            $response->addAction($action);

            $actions = array_values($response->getActions());
            $registerAction = $actions[0];
            $this->assertInstanceOf(RegisterAction::class, $registerAction);
            $this->assertEquals(DummyAction::class, $registerAction->getActionClass());
            $this->assertSame($action, $actions[1]);
        });

        $app->handle($request); // Act

        $this->assertTrue($executed);
    }
}
