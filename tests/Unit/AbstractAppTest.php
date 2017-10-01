<?php

namespace PlatformPHP\GlueApps\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PlatformPHP\ComposedViews\Component\Sidebar;
use PlatformPHP\GlueApps\{AbstractApp, AppEvents};
use PlatformPHP\GlueApps\Asset\{GlueAppsScript, AppScript};
use PlatformPHP\GlueApps\Action\{EvalAction, RegisterAction,
    UpdateAttributeAction};
use PlatformPHP\GlueApps\Request\{RequestInterface, Request};
use PlatformPHP\GlueApps\Response\ResponseInterface;
use PlatformPHP\GlueApps\Update\{Update, UpdateInterface, UpdateResultInterface};
use PlatformPHP\GlueApps\Component\AbstractComponent;
use PlatformPHP\GlueApps\Component\Model\{ModelInterface, Model};
use PlatformPHP\GlueApps\Event\Event;
use PlatformPHP\GlueApps\Tests\Unit\Component\{DummyComponent1, DummyComponent2};
use Symfony\Component\EventDispatcher\{EventDispatcherInterface, EventDispatcher};
use function Opis\Closure\{serialize as s, unserialize as u};

class AbstractAppTest extends TestCase
{
    public function getApp($token = 'token1')
    {
        $app = $this->getMockBuilder(AbstractApp::class)
            ->setConstructorArgs(['http://localhost/controller.php'])
            ->setMethods(['getToken'])
            ->getMockForAbstractClass();
        $app->method('getToken')->willReturn($token);

        $app->setStatusCheck(false);

        return $app;
    }

    public function setUp()
    {
        $this->app = $this->getApp();
    }

    public function testTokenStartsWithApp()
    {
        $app = $this->getMockForAbstractClass(
            AbstractApp::class,
            ['http://localhost/controller.php']
        );

        $this->assertStringStartsWith('app', $app->getToken());
    }

    public function test401ResponseWhenRequestTokenDoNotMatch()
    {
        $app = $this->getApp('token1');

        $request = $this->createMock(RequestInterface::class);
        $request->method('getAppToken')->willReturn('token2');

        $response = $app->handle($request);

        $this->assertEquals(401, $response->getCode());
    }

    public function test200ResponseWhenRequestTokenDoMatch()
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('getAppToken')->willReturn($this->app->getToken());

        $response = $this->app->handle($request);

        $this->assertEquals(200, $response->getCode());
    }

    public function providerResponseHasEqualAppTokenThatAppInAllCases()
    {
        return [
            ['token1', 'token2'],
            ['token1', 'token1'],
        ];
    }

    /**
     * @dataProvider providerResponseHasEqualAppTokenThatAppInAllCases
     */
    public function testResponseHasEqualAppTokenThatAppInAllCases($appToken, $requestToken)
    {
        $app = $this->getApp($appToken);

        $request = $this->createMock(RequestInterface::class);
        $request->method('getAppToken')->willReturn($requestToken);

        $response = $app->handle($request);

        $this->assertEquals($app->getToken(), $response->getAppToken());
    }

    public function testOn_IsShortcutToAddListenerOnDispatcher()
    {
        $eventName = 'app.request';
        $callback = function () {};

        $dispatcher = $this->getMockBuilder(EventDispatcherInterface::class)
            ->setMethods(['addListener'])
            ->getMockForAbstractClass();
        $dispatcher->expects($this->once())
            ->method('addListener')
            ->with(
                $this->equalTo($eventName),
                $this->isType('callable')
            );

        $this->app->setDispatcher($dispatcher);

        $this->app->on($eventName, $callback);
    }

    /**
     * Prueba el evento que permite modificar una solicitud antes
     * de procesarla.
     */
    public function testRequestEventCanChangeTheRequest()
    {
        $originalRequest = $this->createMock(RequestInterface::class);
        $changedRequest = $this->createMock(RequestInterface::class);

        $app = $this->getMockBuilder(AbstractApp::class)
            ->setConstructorArgs([''])
            ->setMethods(['processRequest'])
            ->getMockForAbstractClass();
        $app->expects($this->once())
            ->method('processRequest')
            ->with($this->equalTo($changedRequest));

        $app->on(AppEvents::REQUEST, function ($event) use ($changedRequest) {
            $event->setRequest($changedRequest);
        });

        $app->handle($originalRequest);
    }

    public function testResponseEventCanChangeTheResponse()
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $this->app->on(AppEvents::RESPONSE, function ($event) use ($response) {
            $event->setResponse($response);
        });

        $this->assertSame($response, $this->app->handle($request));
    }

    public function testTheResponseHasOneUpdateResultForEachUpdatePrefixedWithResultInTheId()
    {
        $update1 = $this->createMock(UpdateInterface::class);
        $update1->method('getId')->willReturn('update1');

        $update2 = $this->createMock(UpdateInterface::class);
        $update2->method('getId')->willReturn('update2');

        $updates = [
            'update1' => $update1,
            'update2' => $update2,
        ];

        $request = $this->createMock(RequestInterface::class);
        $request->method('getAppToken')->willReturn($this->app->getToken());
        $request->method('getServerUpdates')->willReturn($updates);

        $response = $this->app->handle($request);
        $results = $response->getUpdateResults();
        $result1 = $results['result_update1'];
        $result2 = $results['result_update2'];

        $this->assertContainsOnlyInstancesOf(
            UpdateResultInterface::class, $results
        );
        $this->assertSame($update1, $result1->getUpdate());
        $this->assertSame($update2, $result2->getUpdate());
    }

    public function testUpdateResultHasErrorWhenComponentNotFound()
    {
        $update1 = $this->createMock(UpdateInterface::class);
        $update1->method('getId')->willReturn('update1');
        $update1->method('getComponentId')->willReturn('component1');

        $request = $this->createMock(RequestInterface::class);
        $request->method('getAppToken')->willReturn($this->app->getToken());
        $request->method('getServerUpdates')->willReturn(['update1' => $update1]);

        $response = $this->app->handle($request);
        $result1 = $response->getUpdateResults()['result_update1'];

        $this->assertArrayHasKey('component-not-found', $result1->getErrors());
    }

    public function testUpdateResultHasErrorWhenSetterMethodThrowError()
    {
        $component1 = $this->getMockBuilder(AbstractComponent::class)
            ->setMethods(['setAttribute1'])
            ->getMockForAbstractClass();
        $component1->method('setAttribute1')
            ->will($this->throwException(new \Exception()));

        $app = $this->getMockBuilder(AbstractApp::class)
            ->setConstructorArgs([''])
            ->setMethods(['getComponent'])
            ->getMockForAbstractClass();
        $app->expects($this->once())
            ->method('getComponent')
            ->with($this->equalTo('component1'))
            ->willReturn($component1);
        $app->setStatusCheck(false);

        $update1 = $this->createMock(UpdateInterface::class);
        $update1->method('getId')->willReturn('update1');
        $update1->method('getComponentId')->willReturn('component1');
        $update1->method('getData')->willReturn(['setAttribute1' => 1]);

        $request = $this->createMock(RequestInterface::class);
        $request->method('getAppToken')->willReturn($app->getToken());
        $request->method('getServerUpdates')->willReturn(['update1' => $update1]);

        $response = $app->handle($request);
        $result1 = $response->getUpdateResults()['result_update1'];
        $errors = $result1->getErrors();

        $this->assertArrayHasKey('setAttribute1', $errors);
    }

    public function testAllUpdatesAreExecutedWithoutErrors()
    {
        $component1 = $this->getMockBuilder(AbstractComponent::class)
            ->setMethods(['setAttribute1', 'setAttribute2'])
            ->getMockForAbstractClass();
        $component1->expects($this->once())
            ->method('setAttribute1')
            ->with($this->equalTo(1));
        $component1->expects($this->once())
            ->method('setAttribute2')
            ->with($this->equalTo(2));

        $model = $this->getMockBuilder(ModelInterface::class)
            ->setMethods(['getSetter'])
            ->getMockForAbstractClass();
        $model->expects($this->exactly(2))
            ->method('getSetter')
            ->withConsecutive(
                [$this->equalTo('attr1')],
                [$this->equalTo('attr2')]
            )
            ->will($this->onConsecutiveCalls(
                'setAttribute1', 'setAttribute2'
            ));

        Model::set(get_class($component1), $model);

        $app = $this->getMockBuilder(AbstractApp::class)
            ->setConstructorArgs([''])
            ->setMethods(['getComponent'])
            ->getMockForAbstractClass();
        $app->expects($this->once())
            ->method('getComponent')
            ->with($this->equalTo('component1'))
            ->willReturn($component1);
        $app->setStatusCheck(false);

        $data = [
            'attr1' => 1,
            'attr2' => 2,
        ];

        $update1 = $this->createMock(UpdateInterface::class);
        $update1->method('getId')->willReturn('update1');
        $update1->method('getComponentId')->willReturn('component1');
        $update1->method('getData')->willReturn($data);

        $request = $this->createMock(RequestInterface::class);
        $request->method('getAppToken')->willReturn($app->getToken());
        $request->method('getServerUpdates')->willReturn(['update1' => $update1]);

        $response = $app->handle($request);
        $result1 = $response->getUpdateResults()['result_update1'];

        $this->assertEquals([], $result1->getErrors());
    }

    public function testEventIsDispatchedOnRequest()
    {
        $eventName = 'event.name';

        $mock = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['callback'])
            ->getMock();
        $mock->expects($this->once())
            ->method('callback');

        $this->app->on($eventName, [$mock, 'callback']);

        $request = $this->createMock(RequestInterface::class);
        $request->method('getAppToken')->willReturn($this->app->getToken());
        $request->method('getEventName')->willReturn($eventName);

        $this->app->handle($request);
    }

    public function testHasStatusCheck_ReturnFalseByDefault()
    {
        $app = $this->getMockForAbstractClass(AbstractApp::class, ['']);

        $this->assertFalse($app->hasStatusCheck());
    }

    public function testHasStatusCheck_ReturnStatusCheckArgument()
    {
        $app = $this->getMockBuilder(AbstractApp::class)
            ->setConstructorArgs(['', '', null, false])
            ->getMockForAbstractClass();

        $this->assertFalse($app->hasStatusCheck());
    }

    public function testHasStatusCheck_ReturnInsertedValueBySetStatusCheck()
    {
        $app = $this->getMockForAbstractClass(AbstractApp::class, ['']);

        $app->setStatusCheck(false);

        $this->assertFalse($app->hasStatusCheck());
    }

    public function initializeAppAndRequestWithDifferentStatus()
    {
        $app = $this->getMockBuilder(AbstractApp::class)
            ->setConstructorArgs([''])
            ->setMethods(['getStatus'])
            ->getMockForAbstractClass();
        $app->method('getStatus')->willReturn('status1');

        $request = $this->createMock(RequestInterface::class);
        $request->method('getAppToken')->willReturn($app->getToken());
        $request->method('getStatus')->willReturn('status2');

        $this->app = $app;
        $this->request = $request;
    }

    public function test409ResponseWhenStatusCheckFail()
    {
        $this->initializeAppAndRequestWithDifferentStatus();
        $this->app->setStatusCheck(true);

        $response = $this->app->handle($this->request);

        $this->assertEquals(409, $response->getCode());
    }

    public function testStatusCheckIsIgnoredWhenValueOfStatusCheckIsFalse()
    {
        $this->initializeAppAndRequestWithDifferentStatus();

        $this->app->setStatusCheck(false);
        $response = $this->app->handle($this->request);

        $this->assertNotEquals(409, $response->getCode());
    }

    public function testGetSnapshot_ReturnAnEmptyArrayByDefault()
    {
        $this->assertEquals([], $this->app->getSnapshot());
    }

    public function testGetSnapshot_ReturnAnArrayWithTheComponentValues()
    {
        $component1 = $this->getMockBuilder(DummyComponent1::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getAttr1', 'getAttr2', 'getMyAttr3'])
            ->getMock();
        $component1->method('getId')->willReturn('component1');
        $component1->method('getAttr1')->willReturn(1);
        $component1->method('getAttr2')->willReturn(2);
        $component1->method('getMyAttr3')->willReturn(3);

        $component2 = $this->getMockBuilder(DummyComponent2::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getAttr4'])
            ->getMock();
        $component2->method('getId')->willReturn('component2');
        $component2->method('getAttr4')->willReturn(4);

        $app = $this->getMockBuilder(AbstractApp::class)
            ->setConstructorArgs([''])
            ->setMethods(['components'])
            ->getMockForAbstractClass();
        $app->method('components')->willReturn([$component1, $component2]);

        $expected = [
            'component1' => [
                'attr1' => 1,
                'attr2' => 2,
                'attr3' => 3,
            ],
            'component2' => [
                'attr4' => 4
            ],
        ];

        $this->assertEquals($expected, $app->getSnapshot());
    }

    public function testResponseHasClientUpdateWhenTheComponentDataIsChangeOnRequestProcess()
    {
        $app = $this->getMockBuilder(AbstractApp::class)
            ->setConstructorArgs([''])
            ->setMethods(['sidebars'])
            ->getMockForAbstractClass();
        $app->method('sidebars')->willReturn(['sidebar1']);
        $app->__construct('');
        $app->setStatusCheck(false);
        $app->on('event1', function ($event) use ($app) {
            $app->getComponent('component1')->setName('daniel', false);
        });

        $component1 = new class('component1') extends AbstractComponent {

            /**
             * @Glue
             */
            protected $name = '';
        };

        $sidebar1 = $app->getSidebar('sidebar1');
        $sidebar1->addComponent($component1);

        $request = $this->createMock(RequestInterface::class);
        $request->method('getAppToken')->willReturn($app->getToken());
        $request->method('getStatus')->willReturn(null);
        $request->method('getEventName')->willReturn('event1');

        $response = $app->handle($request);
        $clientUpdates = $response->getClientUpdates();
        $clientUpdate = array_shift($clientUpdates);

        $this->assertEquals('component1', $clientUpdate->getComponentId());
        $this->assertEquals(['name' => 'daniel'], $clientUpdate->getData());
    }

    public function testTheTriggeredEventContainsTheApp()
    {
        $app = $this->getApp();

        $app->on('event.name', function ($event) use ($app) {
            $this->assertSame($app, $event->getApp());
        });

        $request = $this->createMock(RequestInterface::class);
        $request->method('getAppToken')->willReturn($app->getToken());
        $request->method('getEventName')->willReturn('event.name');

        $app->handle($request);
    }

    public function testGetRequest_ReturnNullByDefault()
    {
        $this->assertNull($this->app->getRequest());
    }

    public function testGetRequest_ReturnTheCurrentRequestBeforeSentTheResponse()
    {
        $app = $this->getApp('token1');

        $request = $this->createMock(RequestInterface::class);
        $request->method('getAppToken')->willReturn('token1');
        $request->method('getEventName')->willReturn('event.name');

        $app->on('event.name', function ($event) use ($app, $request) {
            $this->assertSame($request, $event->getApp()->getRequest());
        });

        $app->handle($request);
    }

    public function testGetRequest_ReturnNullAfterSentTheResponse()
    {
        $app = $this->getApp('token1');

        $request = $this->createMock(RequestInterface::class);
        $request->method('getAppToken')->willReturn('token1');
        $request->method('getEventName')->willReturn('event.name');

        $app->handle($request);

        $this->assertNull($app->getRequest());
    }

    public function testGetResponse_ReturnNullByDefault()
    {
        $this->assertNull($this->app->getResponse());
    }

    public function testGetResponse_ReturnTheResponseForSentBeforeTerminateTheRequestHandling()
    {
        $app = $this->getApp('token1');

        $request = $this->createMock(RequestInterface::class);
        $request->method('getAppToken')->willReturn('token1');
        $request->method('getEventName')->willReturn('event.name');

        $app->on('event.name', function ($event) use ($app) {
            $this->assertInstanceOf(
                ResponseInterface::class,
                $event->getApp()->getResponse()
            );
        });

        $app->handle($request);
    }

    public function testGetResponse_ReturnNullWhenResponseIsSent()
    {
        $app = $this->getApp('token1');

        $request = $this->createMock(RequestInterface::class);
        $request->method('getAppToken')->willReturn('token1');
        $request->method('getEventName')->willReturn('event.name');

        $app->handle($request);

        $this->assertNull($app->getResponse());
    }

    public function testTheResponseKnowTheApp()
    {
        $app = $this->getApp('token1');

        $request = $this->createMock(RequestInterface::class);
        $request->method('getAppToken')->willReturn('token1');
        $request->method('getEventName')->willReturn('event.name');

        $app->on('event.name', function ($event) use ($app) {
            $response = $event->getApp()->getResponse();

            $this->assertSame($app, $response->getApp());
        });

        $app->handle($request);
    }

    public function testBaseUrl_ReturnBaseUrlArgument()
    {
        $baseUrl = uniqid();
        $app = $this->getMockBuilder(AbstractApp::class)
            ->setConstructorArgs(['', $baseUrl])
            ->getMockForAbstractClass();

        $this->assertEquals($baseUrl, $app->baseUrl());
    }

    public function testTriggeredEventHasHisInformation()
    {
        $eventName = 'event.name';
        $eventData = ['data1' => 'value1'];
        $executed  = false;

        $app = $this->getApp();
        $app->on($eventName, function (Event $event) use ($eventName, $eventData, &$executed)
        {
            $this->assertEquals($eventName, $event->getName());
            $this->assertEquals($eventData, $event->getData());

            $executed = true;
        });

        $request = $this->createMock(RequestInterface::class);
        $request->method('getAppToken')->willReturn($app->getToken());
        $request->method('getEventName')->willReturn($eventName);
        $request->method('getEventData')->willReturn($eventData);

        $app->handle($request);

        // Pasa si se el callback fué ejecutado.
        // Importante tener en cuenta que dentro del callback también hay aciertos.
        $this->assertTrue($executed);
    }

    public function testCanSendActions_ReturnTrueByDefault()
    {
        $this->assertTrue($this->app->canSendActions());
    }

    public function testCanSendActions_ReturnInsertedValueBySetSendActions()
    {
        $this->app->setSendActions(false);

        $this->assertFalse($this->app->canSendActions());
    }

    public function testTheResponseCanSendActionsWhenTheAppCanSendActions()
    {
        $app = $this->getApp();

        $request = $this->createMock(RequestInterface::class);
        $request->method('getAppToken')->willReturn('token1');
        $request->method('getEventName')->willReturn('event.name');

        $app->on('event.name', function ($event) use ($app) {
            $response = $event->getApp()->getResponse();
            $this->assertTrue($response->canSendActions());
        });

        $app->handle($request);
    }

    public function testTheResponseDoNotCanSendActionsWhenTheAppDoNotCanSendActions()
    {
        $app = $this->getApp();
        $app->setSendActions(false);

        $request = $this->createMock(RequestInterface::class);
        $request->method('getAppToken')->willReturn('token1');
        $request->method('getEventName')->willReturn('event.name');

        $app->on('event.name', function ($event) use ($app) {
            $response = $event->getApp()->getResponse();
            $this->assertFalse($response->canSendActions());
        });

        $app->handle($request);
    }

    public function testHasActionClass_ReturnFalseWhenActionIsNotRegistered()
    {
        $this->assertFalse($this->app->hasActionClass('ActionClass'));
    }

    public function testHasActionClass_ReturnTrueWhenActionIsRegistered()
    {
        $this->app->registerActionClass('ActionClass');

        $this->assertTrue($this->app->hasActionClass('ActionClass'));
    }

    public function testRegisterActionClass_AddTheActionClassToActionMap()
    {
        $this->app->registerActionClass('ActionClass1');

        $actionClasses = $this->app->getActionClasses();

        $this->assertInternalType('string', $actionClasses['ActionClass1']);
    }

    public function testRegisterActionClass_CanSpecifyTheFrontHandlerId()
    {
        $this->app->registerActionClass('ActionClass2', 'action2');

        $actionClasses = $this->app->getActionClasses();

        $this->assertEquals('action2', $actionClasses['ActionClass2']);
    }

    public function testContainsARegisteredGlueAppsScriptByDefault()
    {
        $this->assertInstanceOf(GlueAppsScript::class, $this->app->getAsset('glueapps'));
    }

    public function testGlueAppsScriptKnowTheApp()
    {
        $glueAppsScript = $this->app->getAsset('glueapps');

        $this->assertEquals($this->app, $glueAppsScript->getApp());
    }

    public function testContainsAnRegisteredAppScriptByDefault()
    {
        $this->assertInstanceOf(AppScript::class, $this->app->getAsset('app'));
    }

    public function testAppScriptDependOfGlueApps()
    {
        $assets = array_values($this->app->getAssets());

        $this->assertEquals('glueapps', $assets[0]->getId());
        $this->assertEquals('app', $assets[1]->getId());
    }

    public function testAppScriptKnowTheApp()
    {
        $appScript = $this->app->getAsset('app');

        $this->assertEquals($this->app, $appScript->getApp());
    }

    public function testGetId_ReturnAppByDefault()
    {
        $this->assertEquals('app', $this->app->getId());
    }

    public function testSetId_ChangeTheAppId()
    {
        $this->app->setId('app1');

        $this->assertEquals('app1', $this->app->getId());
    }

    public function testGetControllerPath_ReturnTheFirstArgument()
    {
        $path = uniqid();
        $app = $this->getMockBuilder(AbstractApp::class)
            ->setConstructorArgs([$path])
            ->getMockForAbstractClass();

        $this->assertEquals($path, $app->getControllerPath());
    }

    public function testGetActionHandler_ReturnNullIfActionClassNotExists()
    {
        $this->assertNull($this->app->getActionHandler('ActionClass1'));
    }

    public function testGetActionHandler_ReturnTheValueWhenActionClassExists()
    {
        $id = uniqid('action1');
        $this->app->registerActionClass('ActionClass1', $id);

        $this->assertEquals($id, $this->app->getActionHandler('ActionClass1'));
    }

    public function testRegisterComponentClass_AddTheComponentClassToTheComponentMap()
    {
        $this->app->registerComponentClass('ComponentClass1');

        $componentClasses = $this->app->getComponentClasses();

        $this->assertInternalType('string', $componentClasses['ComponentClass1']);
    }

    public function testRegisterComponentClass_CanSpecifyTheFrontEndClassName()
    {
        $this->app->registerComponentClass('ComponentClass1', 'Component1');

        $componentClasses = $this->app->getComponentClasses();

        $this->assertEquals('Component1', $componentClasses['ComponentClass1']);
    }

    public function testASidebarIsCreatedByDefaultWithNameEqualToBody()
    {
        $this->assertInstanceOf(Sidebar::class, $this->app->getSidebar('body'));
    }

    public function testHasComponentClass_ReturnFalseByDefault()
    {
        $this->assertFalse($this->app->hasComponentClass('ComponentClass1'));
    }

    public function testHasComponentClass_ReturnTrueWhenClassAlreadyIsRegistered()
    {
        $this->app->registerComponentClass('ComponentClass1');

        $this->assertTrue($this->app->hasComponentClass('ComponentClass1'));
    }

    public function testUpdateComponentClasses_RegisterAllTheComponentClassesInTheFrontEndMap()
    {
        $component1 = new class('component1') extends AbstractComponent {

            public function html(): ?string
            {
            }
        };

        $class1 = get_class($component1);
        $body = $this->app->getSidebar('body');
        $body->addComponent($component1);

        $this->app->updateComponentClasses();

        $classes = $this->app->getComponentClasses();
        $this->assertTrue(isset($classes[$class1]));
    }

    public function testNotExistsClientUpdateWhenComponentDataIsNotChanged()
    {
        $componentId = uniqid('component');
        $component1 = new class($componentId) extends AbstractComponent {
            public function html(): ?string {}
        };

        $body = $this->app->getSidebar('body');
        $body->addComponent($component1);

        $request = new Request($this->app->getToken(), null, 'event1');
        $response = $this->app->handle($request);

        $this->assertEmpty($response->getClientUpdates());
    }

    public function testEventListenersCanBeSerialized()
    {
        $secret = uniqid();
        $closure = function($event) use ($secret) {
            $event->secret = $secret;
        };

        $this->app->on('eventName', $closure);
        $serialization = s($this->app);

        $app = u($serialization);
        $event = new Event($app, 'eventName', []);
        $app->getDispatcher()->dispatch('eventName', $event);

        $this->assertEquals($secret, $event->secret);
    }

    public function testRunServerUpdate_CallToBeforeUpdateMethodOnComponentsWhenExists()
    {
        $executed = false;
        $id       = uniqid();
        $default  = uniqid();
        $new      = uniqid();
        $updateId = uniqid();

        $update = $this->createMock(UpdateInterface::class);
        $update->method('getId')->willReturn($updateId);
        $update->method('getComponentId')->willReturn($id);
        $update->method('getData')->willReturn(['attr' => $new]);

        $testData = [
            'test'     => $this,
            'executed' => &$executed,
            'default'  => $default,
            'new'      => $new,
            'update'   => $update,
        ];

        $component = new class($id, $testData) extends AbstractComponent
        {
            protected $testData;

            /**
             * @Glue
             */
            protected $attr;

            public function __construct($id, $testData)
            {
                parent::__construct($id);
                $this->testData = $testData;
                $this->attr = $testData['default'];
            }

            public function beforeUpdate(UpdateInterface $update)
            {
                $testData = $this->testData;
                $testData['executed'] = true;
                $test = $testData['test'];

                $test->assertEquals($testData['default'], $this->attr);
                $test->assertEquals($update, $testData['update']);
            }
        };

        $this->app->appendComponent('body', $component);

        $request = $this->createMock(RequestInterface::class);
        $request->method('getAppToken')->willReturn($this->app->getToken());
        $request->method('getServerUpdates')->willReturn([$updateId => $update]);

        $this->app->setSendActions(false);
        $this->app->handle($request);

        $this->assertTrue($executed);
    }

    public function testRunServerUpdate_CallToAfterUpdateMethodOnComponentsWhenExists()
    {
        $executed = false;
        $id       = uniqid();
        $default  = uniqid();
        $new      = uniqid();
        $updateId = uniqid();

        $update = $this->createMock(UpdateInterface::class);
        $update->method('getId')->willReturn($updateId);
        $update->method('getComponentId')->willReturn($id);
        $update->method('getData')->willReturn(['attr' => $new]);

        $testData = [
            'test'     => $this,
            'executed' => &$executed,
            'default'  => $default,
            'new'      => $new,
            'update'   => $update,
        ];

        $component = new class($id, $testData) extends AbstractComponent
        {
            protected $testData;

            /**
             * @Glue
             */
            protected $attr;

            public function __construct($id, $testData)
            {
                parent::__construct($id);
                $this->testData = $testData;
                $this->attr = $testData['default'];
            }

            public function afterUpdate(UpdateInterface $update)
            {
                $testData = $this->testData;
                $testData['executed'] = true;
                $test = $testData['test'];

                $test->assertEquals($testData['new'], $this->attr);
                $test->assertEquals($update, $testData['update']);
            }
        };

        $this->app->appendComponent('body', $component);

        $request = $this->createMock(RequestInterface::class);
        $request->method('getAppToken')->willReturn($this->app->getToken());
        $request->method('getServerUpdates')->willReturn([$updateId => $update]);

        $this->app->setSendActions(false);
        $this->app->handle($request);

        $this->assertTrue($executed);
    }

    public function testEvalActionIsRegisteredByDefault()
    {
        $this->assertTrue($this->app->hasActionClass(EvalAction::class));
    }

    public function testRegisterActionActionIsRegisteredByDefault()
    {
        $this->assertTrue($this->app->hasActionClass(RegisterAction::class));
    }

    public function testUpdateAttributeActionIsRegisteredByDefault()
    {
        $this->assertTrue($this->app->hasActionClass(UpdateAttributeAction::class));
    }

    public function testTheResponseDoNotIncludeClientUpdatesWhenAlreadyHasBeenSentActionUpdates()
    {
        $eventName = uniqid('event');
        $componentId = uniqid('component');
        $value = uniqid();
        $testExecuted = false;

        $component = new class($componentId) extends AbstractComponent {

            /**
             * @Glue
             */
            protected $attr;
        };

        $this->app->appendComponent('body', $component);
        $this->app->setSendActions(false);
        $this->app->on($eventName, function($event) use (&$testExecuted, $componentId, $value) {
            $testExecuted = true;
            $app = $event->getApp();
            $component = $app->getComponent($componentId);
            $component->setAttr($value);
        });

        $request = $this->createMock(RequestInterface::class);
        $request->method('getAppToken')->willReturn($this->app->getToken());
        $request->method('getEventName')->willReturn($eventName);

        $response = $this->app->handle($request);

        $actions = $response->getActions();
        $action = array_pop($actions);

        $this->assertTrue($testExecuted);
        $this->assertEmpty($response->getClientUpdates());

        $this->assertInstanceOf(UpdateAttributeAction::class, $action);
        $this->assertEquals($componentId, $action->getComponentId());
        $this->assertEquals('attr', $action->getAttribute());
        $this->assertEquals($value, $action->getValue());
    }

    public function providerControllerPath()
    {
        return [
            [null], [uniqid()]
        ];
    }

    /**
     * @dataProvider providerControllerPath
     */
    public function testGetControllerPath_ReturnInsertedValueBySetControllerPath($controller)
    {
        $app = $this->getMockForAbstractClass(AbstractApp::class, ['']);
        $app->setControllerPath($controller);

        $this->assertEquals($controller, $app->getControllerPath());
    }

    public function testRegisterProcessorClass_AddTheProcessorClassToTheProcessorMap()
    {
        $class = uniqid();
        $this->app->registerProcessorClass($class);

        $classes = $this->app->getProcessorClasses();

        $this->assertInternalType('string', $classes[$class]);
    }

    public function testRegisterProcessorClass_CanSpecifyTheFrontEndClassName()
    {
        $class = uniqid();
        $key = uniqid();
        $this->app->registerProcessorClass($class, $key);

        $this->assertEquals($key, $this->app->getFrontProcessorClass($class));
    }

    public function testGetProcessorClasses_ReturnAnArrayWithAllRegisteredClasses()
    {
        $class = uniqid();
        $this->app->registerProcessorClass($class);

        $this->assertArrayHasKey($class, $this->app->getProcessorClasses());
    }

    public function testIsBooted_ReturnFalseByDefault()
    {
        $this->assertFalse($this->app->isBooted());
    }

    public function testIsBooted_ReturnTrueAfterPrintCall()
    {
        $this->app->print();

        $this->assertTrue($this->app->isBooted());
    }

    public function testIsBooted_ReturnInstedValueBySetBooted()
    {
        $this->app->setBooted(true);

        $this->assertTrue($this->app->isBooted());
    }

    public function testIsDebug_ReturnFalseByDefault()
    {
        $this->assertFalse($this->app->isDebug());
    }

    public function testIsDebug_ReturnInsertedValueBySetDebug()
    {
        $this->app->setDebug(true);

        $this->assertTrue($this->app->isDebug());
    }

    public function testSetDebug_MinimizeTheGlueAppsScript()
    {
        $this->app->setDebug(true);
        $glueAppsScript = $this->app->getAsset('glueapps');

        $this->assertFalse($glueAppsScript->isMinimized());
    }

    public function testSetDebug_MinimizeTheAppScript()
    {
        $this->app->setDebug(true);
        $appScript = $this->app->getAsset('app');

        $this->assertFalse($appScript->isMinimized());
    }

    public function testInProcess_ReturnFalseByDefault()
    {
        $this->assertFalse($this->app->inProcess());
    }

    public function testInProcess_ReturnTrueWhenARequestIsInProcess()
    {
        $executed = false;
        $eventName = 'event1';

        $request = $this->createMock(RequestInterface::class);
        $request->method('getAppToken')->willReturn($this->app->getToken());
        $request->method('getEventName')->willReturn($eventName);

        $this->app->on($eventName, function () use (&$executed) {
            $executed = true;
            $this->assertTrue($this->app->inProcess());
        });

        $this->app->handle($request);

        $this->assertTrue($executed);
    }
}
