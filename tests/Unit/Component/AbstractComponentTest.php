<?php

namespace Andaniel05\GluePHP\Tests\Unit\Component;

use PHPUnit\Framework\TestCase;
use Andaniel05\GluePHP\AbstractApp;
use Andaniel05\GluePHP\Action\AbstractAction;
use Andaniel05\GluePHP\Action\UpdateAction;
use Andaniel05\GluePHP\Component\AbstractComponent;
use Andaniel05\GluePHP\Component\Model\Model;
use Andaniel05\GluePHP\Component\Model\ModelInterface;
use Andaniel05\GluePHP\Response\Response;
use Andaniel05\GluePHP\Processor\BindValueProcessor;
use Andaniel05\GluePHP\Processor\BindEventsProcessor;
use Andaniel05\GluePHP\Processor\BindAttributesProcessor;
use Andaniel05\GluePHP\Processor\BindHtmlProcessor;
use Andaniel05\GluePHP\Processor\ShortEventsProcessor;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class AbstractComponentTest extends TestCase
{
    public function setUp()
    {
        $this->component = $this->getMockForAbstractClass(
            AbstractComponent::class,
            ['component']
        );
    }

    public function testComponentIdStartWithLowerBaseNameOfComponentClass()
    {
        $component = new DummyComponent1;

        $this->assertStringStartsWith('dummycomponent1', $component->getId());
    }

    public function testGetApp_ReturnInsertedValueBySetApp()
    {
        $app = $this->getMockForAbstractClass(AbstractApp::class, ['']);
        $component = $this->getMockForAbstractClass(
            AbstractComponent::class,
            ['component']
        );

        $component->setApp($app);

        $this->assertSame($app, $component->getApp());
    }

    public function testGetPage_ReturnInsertedValueBySetApp()
    {
        $app = $this->getMockForAbstractClass(AbstractApp::class, ['']);
        $component = $this->getMockForAbstractClass(
            AbstractComponent::class,
            ['component']
        );

        $component->setApp($app);

        $this->assertSame($app, $component->getPage());
    }

    public function testBasePath_ReturnAnEmptyStringByDefault()
    {
        $component = $this->getMockForAbstractClass(AbstractComponent::class);

        $this->assertEquals('', $component->basePath());
    }

    public function testBasePath_IsShortcutToBasePathFromApp()
    {
        $basePath = uniqid();
        $app = $this->getMockBuilder(AbstractApp::class)
            ->disableOriginalConstructor()
            ->setMethods(['basePath'])
            ->getMockForAbstractClass();
        $app->expects($this->once())
            ->method('basePath')
            ->with($this->equalTo('script.js'))
            ->willReturn('http://localhost/script.js');

        $component = $this->getMockForAbstractClass(AbstractComponent::class);
        $component->setApp($app);

        $this->assertEquals('http://localhost/script.js', $component->basePath('script.js'));
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

    /**
     * @expectedException Andaniel05\GluePHP\Component\Exception\InvalidCallException
     */
    public function testThrowAnInvalidCallException_OnSetOperationWhenGlueAttributeNotExists()
    {
        $component = new class extends AbstractComponent {};

        $component->setData();
    }

    /**
     * @expectedException Andaniel05\GluePHP\Component\Exception\InvalidCallException
     */
    public function testThrowAnInvalidCallException_OnGetOperationWhenGlueAttributeNotExists()
    {
        $component = new class extends AbstractComponent {};

        $component->getData();
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
        $closure = function () {
        };

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
            AbstractComponent::class,
            [$componentId]
        );
        $component->setApp($app);

        // Act
        $component->on($eventName, $closure);
    }

    public function testOn_RegisterTheEventInTheComponentDispatcher()
    {
        $executed = false;
        $eventName = uniqid();

        $listener = function () use (&$executed) {
            $executed = true;
        };

        $this->component->on($eventName, $listener);

        $this->component->getDispatcher()->dispatch($eventName);

        $this->assertTrue($executed);
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

    public function testSetPage_SetTheAppAndThePage()
    {
        $page = $this->createMock(AbstractApp::class);
        $component = $this->getMockForAbstractClass(
            AbstractComponent::class,
            ['component']
        );

        $component->setPage($page);

        $this->assertAttributeEquals($page, 'page', $component);
        $this->assertAttributeEquals($page, 'app', $component);
    }

    public function testRenderizeChildren_WrapTheChildrenInsideADivContainer()
    {
        $parentId = uniqid('parent');
        $parent = $this->getMockForAbstractClass(
            AbstractComponent::class,
            [$parentId]
        );

        $html = uniqid();
        $childId = uniqid('child');
        $child = $this->createMock(AbstractComponent::class);
        $child->method('getId')->willReturn($childId);
        $child->method('html')->willReturn($html);
        $childView = AbstractComponent::containerView($childId, $html);

        $parent->addChild($child);

        $expected = <<<HTML
<div class="gphp-children gphp-{$parentId}-children">
    {$childView}
</div>
HTML;

        $this->assertXmlStringEqualsXmlString(
            $expected,
            $parent->renderizeChildren()
        );
    }

    public function testDependsOfBindValueProcessor()
    {
        $this->assertContains(
            BindValueProcessor::class,
            $this->component->processors()
        );
    }

    public function testDependsOfBindEventsProcessor()
    {
        $this->assertContains(
            BindEventsProcessor::class,
            $this->component->processors()
        );
    }

    public function testDependsOfBindAttributesProcessor()
    {
        $this->assertContains(
            BindAttributesProcessor::class,
            $this->component->processors()
        );
    }

    public function testDependsOfBindHtmlProcessor()
    {
        $this->assertContains(
            BindHtmlProcessor::class,
            $this->component->processors()
        );
    }

    public function testDependsOfShortEventsProcessor()
    {
        $this->assertContains(
            ShortEventsProcessor::class,
            $this->component->processors()
        );
    }

    public function testGetDispatcher_ReturnInstanceOfSymfonyEventDispatcher()
    {
        $this->assertInstanceOf(
            EventDispatcherInterface::class,
            $this->component->getDispatcher()
        );
    }

    public function testGetDispatcher_ReturnInsertedDispatcherBySetDispatcher()
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->component->setDispatcher($dispatcher);

        $this->assertEquals($dispatcher, $this->component->getDispatcher());
    }

    public function testConstructorScriptIsNullByDefault()
    {
        $component = $this->getMockForAbstractClass(
            AbstractComponent::class,
            ['component']
        );

        $this->assertNull($component->constructorScript());
    }

    public function testEventRecordReturnAnEmptyArrayByDefault()
    {
        $this->assertEquals([], $this->component->getEventRecord());
    }

    public function testDynamicSettersReturnValueFrom_SetMethod()
    {
        $value = uniqid();
        $component = new class($value) extends AbstractComponent {

            /**
             * @Glue
             */
            protected $data;

            public function __construct($value)
            {
                parent::__construct();

                $this->value = $value;
            }

            public function _set(string $attribute, $value, bool $sendAction = true)
            {
                return $this->value;
            }
        };

        $this->assertEquals($value, $component->setData(''));
    }

    public function testDynamicSetterIntokeTo_SetWithSameArguments()
    {
        $value = uniqid();
        $sendAction = (bool) rand(0, 1);

        $component = $this->getMockBuilder(AbstractComponent::class)
            ->setMethods(['_set'])
            ->getMockForAbstractClass();
        $component->expects($this->once())
            ->method('_set')
            ->with(
                $this->equalTo('attr'),
                $this->equalTo($value),
                $this->equalTo($sendAction)
            );

        $model = $this->createMock(ModelInterface::class);
        $model->method('getAttributeList')->willReturn(['attr']);
        Model::set(get_class($component), $model);

        $component->setAttr($value, $sendAction);
    }
}
