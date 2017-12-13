<?php

namespace Andaniel05\GluePHP\Tests\Unit\Extended\Polymer;

use PHPUnit\Framework\TestCase;
use Andaniel05\GluePHP\Extended\Polymer\WebComponent;
use Andaniel05\GluePHP\Extended\Polymer\WebComponentProcessor;
use Andaniel05\ComposedViews\Asset\ScriptAsset;
use Andaniel05\ComposedViews\Asset\ImportAsset;
use Andaniel05\ComposedViews\HtmlElement\HtmlElementInterface;
use Andaniel05\ComposedViews\HtmlElement\HtmlElement;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class WebComponentTest extends TestCase
{
    public function setUp()
    {
        $this->component = new WebComponent('id', 'tag', '');
    }

    public function testTheIdIsTheIdArgument()
    {
        $id = uniqid();

        $component = new WebComponent($id, '', '');

        $this->assertEquals($id, $component->getId());
    }

    public function testTheDefaultIdStartsWithWebComponent()
    {
        $component = new WebComponent(null, '', '');

        $this->assertStringStartsWith('webcomponent', $component->getId());
    }

    public function testTheTagOfTheHtmlElementIsEqualToCustomElementTagArgument()
    {
        $tag = uniqid();

        $component = new WebComponent('component', $tag, '');

        $this->assertEquals($tag, $component->getElement()->getTag());
    }

    public function testSetElementChangeTheElement()
    {
        $element = $this->createMock(HtmlElementInterface::class);

        $this->component->setElement($element);

        $this->assertEquals($element, $this->component->getElement());
    }

    public function testHtmlReturnResultOfHtmlElementHtmlMethod()
    {
        $html = uniqid();

        $element = $this->createMock(HtmlElementInterface::class);
        $element->method('html')->willReturn($html);

        $this->component->setElement($element);

        $this->assertEquals($html, $this->component->html());
    }

    public function testTheContentOfTheHtmlElementIsResultOfRenderizeChildren()
    {
        $childrenHtml = uniqid();
        $component = $this->getMockBuilder(WebComponent::class)
            ->setConstructorArgs(['id', 'tag', 'import'])
            ->setMethods(['renderizeChildren'])
            ->getMock();
        $component->method('renderizeChildren')
            ->willReturn($childrenHtml);

        $element = $this->getMockBuilder(HtmlElement::class)
            ->setMethods(['setContent'])
            ->getMock();
        $element->expects($this->once())
            ->method('setContent')
            ->with($this->equalTo([$childrenHtml]));

        $component->setElement($element);

        $component->html();
    }

    public function testDependsOfPolymerAsset()
    {
        $assets = $this->component->getAssets();

        $polymer = $assets['polymer'];

        $this->assertInstanceOf(ImportAsset::class, $polymer);
        $this->assertStringEndsWith('polymer/polymer-element.html', $polymer->getUri());
    }

    public function testPolymerAssetDependsOfWebComponentsLoader()
    {
        $assets = $this->component->getAssets();
        $polymer = $assets['polymer'];

        $this->assertTrue($polymer->hasDependency('webcomponents-loader'));
    }

    public function testDependsOfWebComponentsLoaderAsset()
    {
        $assets = $this->component->getAssets();

        $webComponentsLoader = $assets['webcomponents-loader'];

        $this->assertInstanceOf(ScriptAsset::class, $webComponentsLoader);
        $this->assertStringEndsWith(
            'webcomponentsjs/webcomponents-loader.js',
            $webComponentsLoader->getUri()
        );
    }

    public function testContainsAnImportAssetsWithUriEqualToImportArgument()
    {
        $importUri = uniqid();
        $component = new WebComponent('id', 'my-tag', $importUri);

        $import = $component->getAssets()['import'];

        $this->assertInstanceOf(ImportAsset::class, $import);
        $this->assertEquals($importUri, $import->getUri());
    }

    public function testTheImportAssetDependsOfPolymer()
    {
        $importUri = uniqid();
        $component = new WebComponent('id', 'my-tag', $importUri);
        $import = $component->getAssets()['import'];

        $this->assertTrue($import->hasDependency('polymer'));
    }

    public function testDependsOfWebComponentProcessor()
    {
        $processors = $this->component->processors();

        $this->assertContains(WebComponentProcessor::class, $processors);
    }

    public function testBindEventsReturnAnEmptyArrayByDefault()
    {
        $this->assertEquals([], $this->component->bindEvents());
    }

    public function testBindEventsReturnTheBindEventsAttribute()
    {
        $bindEvents = [uniqid()];
        setAttr($bindEvents, 'bindEvents', $this->component);

        $this->assertEquals($bindEvents, $this->component->bindEvents());
    }

    public function testBindPropertiesReturnAnEmptyArrayByDefault()
    {
        $this->assertEquals([], $this->component->bindProperties());
    }

    public function testBindPropertiesReturnTheBindPropertiesAttribute()
    {
        $bindProperties = [uniqid()];
        setAttr($bindProperties, 'bindProperties', $this->component);

        $this->assertEquals($bindProperties, $this->component->bindProperties());
    }

    public function provider1()
    {
        $component1 = new class('component1', 'my-tag', '') extends WebComponent {
            public function bindProperties(): array
            {
                $local = uniqid();
                $remote = uniqid();

                return [$local => $remote];
            }
        };

        $component2 = new class('component2', 'my-tag', '') extends WebComponent {
            public function bindProperties(): array
            {
                $local = uniqid();

                return [$local];
            }
        };

        return [
            [$component1],
            [$component2],
        ];
    }

    /**
     * @dataProvider provider1
     * @expectedException Andaniel05\GluePHP\Extended\Polymer\Exception\UndefinedGlueAttributeException
     */
    public function testThrowUndefinedGlueAttributeExceptionWhenBindPropertiesContainsAnUndefinedAttributeName($component)
    {
        $app = new TestApp;
        $app->appendComponent('body', $component);

        $app->html();
    }

    public function testGetTagNameReturnResultOfElementTag()
    {
        $tag = uniqid('tag');
        $element = $this->createMock(HtmlElementInterface::class);
        $element->method('getTag')->willReturn($tag);

        $this->component->setElement($element);

        $this->assertEquals($tag, $this->component->getTagName());
    }

    public function testGetImportUriReturnUriArgument()
    {
        $importUri = uniqid('uri');
        $component = new WebComponent('id', 'tag', $importUri);

        $this->assertEquals($importUri, $component->getImportUri());
    }
}
