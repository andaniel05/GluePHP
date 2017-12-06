<?php

namespace Andaniel05\GluePHP\Tests\Functional\Processor;

use Andaniel05\GluePHP\Component\AbstractComponent;
use Andaniel05\GluePHP\Tests\StaticTestCase;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class BindValueProcessorStaticTest extends StaticTestCase
{
    public function providerGPhpBindValue()
    {
        return [
            ['gphp-bind-value'], ['data-gphp-bind-value']
        ];
    }

    /**
     * @dataProvider providerGPhpBindValue
     */
    public function testGPhpBindValue_ViewToComponent($attribute)
    {
        $component1 = new class($attribute) extends AbstractComponent {

            /**
             * @Glue
             */
            protected $text;

            protected $attribute;

            public function __construct(string $attribute)
            {
                parent::__construct('component1');

                $this->attribute = $attribute;
            }

            public function html(): ?string
            {
                return "<input type=\"text\" {$this->attribute}=\"text\">";
            }
        };

        $this->body->addChild($component1);
        $this->writeDocument($this->app->html());
        $this->script('window.component1 = app.getComponent("component1")');

        $secret = uniqid();
        $script = <<<JAVASCRIPT
var input = document.querySelector('input');
input.value = '{$secret}';
var event = new Event('change');
input.dispatchEvent(event);

JAVASCRIPT;
        $this->script($script);

        $this->assertEquals(
            $secret,
            $this->script("return component1.model.text")
        );
    }

    /**
     * @dataProvider providerGPhpBindValue
     */
    public function testGPhpBindValue_ComponentToView_TheUpdateIsRegistered($attribute)
    {
        $component1 = new class($attribute) extends AbstractComponent {

            /**
             * @Glue
             */
            protected $text;

            protected $attribute;

            public function __construct(string $attribute)
            {
                parent::__construct('component1');

                $this->attribute = $attribute;
            }

            public function html(): ?string
            {
                return "<input type=\"text\" {$this->attribute}=\"text\">";
            }
        };

        $this->body->addChild($component1);
        $this->writeDocument($this->app->html());
        $this->script('window.component1 = app.getComponent("component1")');

        $secret = uniqid();
        $this->script("component1.setText('{$secret}')");

        $this->assertEquals(
            $secret,
            $this->script("return document.querySelector('input').value")
        );

        $this->assertEquals(
            $secret,
            $this->script("return app.buffer.component1.text")
        );
    }
}
