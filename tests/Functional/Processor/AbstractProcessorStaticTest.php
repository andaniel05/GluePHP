<?php

namespace Andaniel05\GluePHP\Tests\Functional;

use Andaniel05\GluePHP\Tests\StaticTestCase;
use Andaniel05\GluePHP\Component\AbstractComponent;
use Andaniel05\GluePHP\Processor\AbstractProcessor;

class AbstractProcessorStaticTest extends StaticTestCase
{
    public function test1()
    {
        $processor = new class extends AbstractProcessor {

            public static function script(): string
            {
                return <<<JAVASCRIPT
    traverseElements(function(element) {
        var attr = document.createAttribute('data-attr');
        attr.value = '';
        element.setAttributeNode(attr);
    });
JAVASCRIPT;
            }
        };

        $processorClass = get_class($processor);

        $component = new class('component', $processorClass) extends AbstractComponent {

            public function __construct($id, $processorClass)
            {
                parent::__construct($id);

                $this->processorClass = $processorClass;
            }

            public function processors(): array
            {
                return [$this->processorClass];
            }

            public function html(): ?string
            {
                return '<div></div>';
            }
        };

        $this->app->appendComponent('body', $component);
        $this->writeDocument($this->app->html());

        $this->assertXmlStringEqualsXmlString(
            "<div data-attr=\"\"></div>",
            $this->script("return app.components['component'].element.innerHTML")
        );
    }
}
