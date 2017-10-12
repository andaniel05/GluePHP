<?php

namespace Andaniel05\GluePHP\Tests\Unit\Component;

use PHPUnit\Framework\TestCase;
use Andaniel05\GluePHP\AbstractApp;
use Andaniel05\GluePHP\Action\{AbstractAction, UpdateAction};
use Andaniel05\GluePHP\Component\AbstractComponent;
use Andaniel05\GluePHP\Component\Model\{Model, ModelInterface};
use Andaniel05\GluePHP\Response\Response;

class AbstractComponentTest extends TestCase
{
    public function testComponentIdStartWithCompPrefix()
    {
        $component = $this->getMockBuilder(AbstractComponent::class)
            ->getMockForAbstractClass();

        $this->assertStringStartsWith('comp_', $component->getId());
    }

    public function testGetApp_ReturnInsertedValueBySetApp()
    {
        $app = $this->getMockForAbstractClass(AbstractApp::class, ['']);
        $component = $this->getMockForAbstractClass(
            AbstractComponent::class, ['component']
        );

        $component->setApp($app);

        $this->assertSame($app, $component->getApp());
    }

    public function testGetPage_ReturnInsertedValueBySetApp()
    {
        $app = $this->getMockForAbstractClass(AbstractApp::class, ['']);
        $component = $this->getMockForAbstractClass(
            AbstractComponent::class, ['component']
        );

        $component->setApp($app);

        $this->assertSame($app, $component->getPage());
    }

    public function testBaseUrl_ReturnAnEmptyStringByDefault()
    {
        $component = $this->getMockForAbstractClass(AbstractComponent::class);

        $this->assertEquals('', $component->baseUrl());
    }

    public function testBaseUrl_IsShortcutToBaseUrlFromApp()
    {
        $baseUrl = uniqid();
        $app = $this->getMockBuilder(AbstractApp::class)
            ->disableOriginalConstructor()
            ->setMethods(['baseUrl'])
            ->getMockForAbstractClass();
        $app->expects($this->once())
            ->method('baseUrl')
            ->with($this->equalTo('script.js'))
            ->willReturn('http://localhost/script.js');

        $component = $this->getMockForAbstractClass(AbstractComponent::class);
        $component->setApp($app);

        $this->assertEquals('http://localhost/script.js', $component->baseUrl('script.js'));
    }

    public function testGetModel_ReturnTheModelFromCache()
    {
        $component = $this->getMockForAbstractClass(AbstractComponent::class);
        $componentClass = get_class($component);

        $model = $component->getModel();

        $this->assertEquals($componentClass, $model->getClass());
        $this->assertSame($model, Model::get($componentClass));
    }

    public function testTheGlueAttributesHasDynamicGettersAndSetters()
    {
        $component = new class extends AbstractComponent {

            /**
             * @Glue
             */
            protected $data;

            public function html(): ?string
            {
            }
        };

        $value = rand();
        $component->setData($value);

        $this->assertAttributeEquals($value, 'data', $component);
        $this->assertEquals($value, $component->getData());
    }

    /**
     * @expectedException Andaniel05\GluePHP\Component\Exception\InvalidCallException
     */
    public function testThrowAnInvalidCallException_WhenMethodNameIsNotRecognizable()
    {
        $component = new class extends AbstractComponent {

            public function html(): ?string
            {
            }
        };

        $component->unexistentMethod();
    }

    public function testDynamicSetterAddAnUpdateActionWhenExistsResponseInAppAndSecondArgumentIsMissing()
    {
        $app = $this->getMockBuilder(AbstractApp::class)
            ->setConstructorArgs([''])
            ->setMethods(['getResponse'])
            ->getMockForAbstractClass();

        $response = new Response($app);
        $response->setSendActions(false);

        $app->method('getResponse')->willReturn($response);

        $value = uniqid();
        $componentId = uniqid();

        $component = new class($componentId) extends AbstractComponent {

            /**
             * @Glue
             */
            protected $attr;
        };

        $component->setApp($app);

        $component->setAttr($value); // Act
        $actions = $response->getActions();
        $action = array_pop($actions);

        $this->assertInstanceOf(UpdateAction::class, $action);
        $this->assertEquals($componentId, $action->getComponentId());
        $this->assertEquals('attr', $action->getAttribute());
        $this->assertEquals($value, $action->getValue());
    }

    public function testDynamicSetterDoNotAddAnUpdateActionWhenSecondArgumentIsFalse()
    {
        $app = $this->getMockBuilder(AbstractApp::class)
            ->setConstructorArgs([''])
            ->setMethods(['getResponse'])
            ->getMockForAbstractClass();

        $response = new Response($app);
        $response->setSendActions(false);

        $app->method('getResponse')->willReturn($response);

        $value = uniqid();
        $componentId = uniqid();

        $component = new class($componentId) extends AbstractComponent {

            /**
             * @Glue
             */
            protected $attr;
        };

        $component->setApp($app);
        $component->setAttr($value, false); // Act

        $this->assertEmpty($response->getActions());
    }

    public function testDynamicSettersReturnToItSelf()
    {
        $component = new class('') extends AbstractComponent {

            /**
             * @Glue
             */
            protected $attr;
        };

        $this->assertEquals($component, $component->setAttr(''));
    }

    public function testOn_RegisterTheEventInTheApp()
    {
        $componentId = uniqid('component');
        $eventName = uniqid('eventName');
        $closure = function () {};

        $app = $this->getMockBuilder(AbstractApp::class)
            ->disableOriginalConstructor()
            ->setMethods(['on'])
            ->getMockForAbstractClass();
        $app->expects($this->once())
            ->method('on')
            ->with(
                $this->equalTo("$componentId.$eventName"),
                $this->equalTo($closure)
            );

        $component = $this->getMockForAbstractClass(
            AbstractComponent::class, [$componentId]
        );
        $component->setApp($app);

        // Act
        $component->on($eventName, $closure);
    }

    public function testAct_InvokeToActOnTheApp()
    {
        $action = $this->createMock(AbstractAction::class);

        $app = $this->getMockBuilder(AbstractApp::class)
            ->disableOriginalConstructor()
            ->setMethods(['act'])
            ->getMockForAbstractClass();
        $app->expects($this->once())
            ->method('act')
            ->with($this->equalTo($action));

        $component = $this->getMockForAbstractClass(AbstractComponent::class);
        $component->setApp($app);

        $component->act($action);
    }

    public function testAct_WithoutErrorsWhenAppDoNotExists()
    {
        $action = $this->createMock(AbstractAction::class);
        $component = $this->getMockForAbstractClass(AbstractComponent::class);

        $component->act($action);
        $this->assertTrue(true);
    }

    public function testSetApp_SetTheValueInTheAppAttribute()
    {
        $app = $this->createMock(AbstractApp::class);
        $component = $this->getMockForAbstractClass(AbstractComponent::class);

        $component->setApp($app);

        $this->assertAttributeEquals($app, 'app', $component);
    }
}
