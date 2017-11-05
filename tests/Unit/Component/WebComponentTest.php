<?php

namespace Andaniel05\GluePHP\Tests\Unit\Component;

use PHPUnit\Framework\TestCase;
use Andaniel05\GluePHP\Component\WebComponent;
use Andaniel05\ComposedViews\HtmlElement\{HtmlElementInterface, HtmlElement};

class WebComponentTest extends TestCase
{
    public function setUp()
    {
        $this->component = new WebComponent('id', 'tag');
    }

    public function testTheIdIsTheIdArgument()
    {
        $id = uniqid();

        $component = new WebComponent($id, '');

        $this->assertEquals($id, $component->getId());
    }

    public function testTheTagOfTheHtmlElementIsEqualToCustomElementTagArgument()
    {
        $tag = uniqid();

        $component = new WebComponent('component', $tag);

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
            ->setConstructorArgs(['id', 'tag'])
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
}
