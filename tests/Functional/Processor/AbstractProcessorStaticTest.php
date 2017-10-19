<?php

namespace Andaniel05\GluePHP\Tests\Functional;

use Andaniel05\GluePHP\Tests\StaticTestCase;
use Andaniel05\GluePHP\Component\AbstractComponent;
use Andaniel05\GluePHP\Processor\AbstractProcessor;

class AbstractProcessorStaticTest extends StaticTestCase
{
    public function provider()
    {
        return [
            [
                $attr = uniqid('data-attr'),
                '<div></div>',
                false,
                "<div {$attr}=\"\"></div>",
            ],
            [
                $attr = uniqid('data-attr'),
                '<div>
                    <div></div>
                </div>',
                false,
                "<div {$attr}=\"\">
                    <div {$attr}=\"\"></div>
                </div>",
            ],
            [
                $attr = uniqid('data-attr'),
                '<div>
                    <div></div>
                    <div></div>
                </div>',
                false,
                "<div {$attr}=\"\">
                    <div {$attr}=\"\"></div>
                    <div {$attr}=\"\"></div>
                </div>",
            ],
            [
                $attr = uniqid('data-attr'),
                '<div>
                    <div class="gphp-children gphp-component-children">
                        <div></div>
                    </div>
                </div>',
                false,
                "<div {$attr}=\"\">
                    <div class=\"gphp-children gphp-component-children\">
                        <div></div>
                    </div>
                </div>",
            ],
            [
                $attr = uniqid('data-attr'),
                '<div>
                    <div class="gphp-children gphp-component-children">
                        <div></div>
                    </div>
                </div>',
                true,
                "<div {$attr}=\"\">
                    <div {$attr}=\"\" class=\"gphp-children gphp-component-children\">
                        <div {$attr}=\"\"></div>
                    </div>
                </div>",
            ],
        ];
    }

    /**
     * @dataProvider provider
     */
    public function testTraverseElements($attr, $html, $includeChildren, $expected)
    {
        $processor = new class extends AbstractProcessor {

            public static $attr;
            public static $includeChildren;

            public static function script(): string
            {
                $attr = static::$attr;
                $includeChildren = static::$includeChildren;

                return <<<JAVASCRIPT
    traverseElements(function(element) {
        var attr = document.createAttribute("{$attr}");
        element.setAttributeNode(attr);
    }, {$includeChildren});
JAVASCRIPT;
            }
        };

        $processorClass = get_class($processor);
        $processorClass::$attr = $attr;
        $processorClass::$includeChildren = $includeChildren;

        $component = new class('component', $processorClass, $html) extends AbstractComponent {

            public function __construct($id, $processorClass, $html)
            {
                parent::__construct($id);

                $this->processorClass = $processorClass;
                $this->html = $html;
            }

            public function processors(): array
            {
                return [$this->processorClass];
            }

            public function html(): ?string
            {
                return $this->html;
            }
        };

        $this->app->appendComponent('body', $component);
        $this->writeDocument($this->app->html());

        $this->assertXmlStringEqualsXmlString(
            $expected,
            $this->script("return app.components['component'].element.innerHTML")
        );
    }
}
