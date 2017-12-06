<?php

namespace Andaniel05\GluePHP\Tests\Functional\Processor;

use Andaniel05\GluePHP\Component\AbstractComponent;
use Andaniel05\GluePHP\Tests\StaticTestCase;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class BindHtmlProcessorStaticTest extends StaticTestCase
{
    public function providerGPhpBindHtml()
    {
        return [
            ['gphp-bind-html'], ['data-gphp-bind-html']
        ];
    }

    /**
     * @dataProvider providerGPhpBindHtml
     */
    public function testHtmlBindingInitializeInnerHTMLOnBoundChilds($attribute)
    {
        $component1 = new class($attribute) extends AbstractComponent {

            /**
             * @Glue
             */
            protected $gAttr;

            protected $attribute;

            public function __construct($attribute)
            {
                parent::__construct('component1');

                $this->attribute = $attribute;
            }

            public function html(): ?string
            {
                return "<div id=\"div\" {$this->attribute}=\"gAttr\"></div>";
            }
        };

        $secret = uniqid();
        $component1->setGAttr($secret);

        $this->body->addChild($component1);
        $this->writeDocument($this->app->html());

        $this->assertEquals(
            $secret,
            $this->script("return document.getElementById('div').innerHTML;")
        );
    }

    /**
     * @dataProvider providerGPhpBindHtml
     */
    public function testHtmlBindingFromTheComponentModelToTheView($attribute)
    {
        $component1 = new class($attribute) extends AbstractComponent {

            /**
             * @Glue
             */
            protected $gAttr;

            protected $attribute;

            public function __construct($attribute)
            {
                parent::__construct('component1');

                $this->attribute = $attribute;
            }

            public function html(): ?string
            {
                return "<div id=\"div\" {$this->attribute}=\"gAttr\"></div>";
            }
        };

        $this->body->addChild($component1);
        $this->writeDocument($this->app->html());

        $secret = uniqid();
        $this->script("app.getComponent('component1').setGAttr('{$secret}')");

        $this->assertEquals(
            $secret,
            $this->script("return document.getElementById('div').innerHTML;")
        );
    }

    // /**
    //  * @dataProvider providerGPhpBindHtml
    //  */
    // public function testHtmlBindingFromTheViewToTheComponentModel($attribute)
    // {
    //     $component1 = new class($attribute) extends AbstractComponent {

    //         /**
    //          * @Glue
    //          */
    //         protected $gAttr;

    //         protected $attribute;

    //         public function __construct($attribute)
    //         {
    //             parent::__construct('component1');

    //             $this->attribute = $attribute;
    //         }

    //         public function html(): ?string
    //         {
    //             return "<div id=\"div\" {$this->attribute}=\"gAttr\"></div>";
    //         }
    //     };

    //     $this->body->addChild($component1);
    //     $this->writeDocument($this->app->html());

    //     $secret = uniqid();
    //     $this->script("document.getElementById('div').innerHTML = '{$secret}'");

    //     $this->assertEquals(
    //         $secret, $this->script("return app.getComponent('component1').model.gAttr;")
    //     );
    // }
}
