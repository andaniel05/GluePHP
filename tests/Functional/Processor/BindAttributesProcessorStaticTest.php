<?php

namespace Andaniel05\GluePHP\Tests\Functional\Processor;

use Andaniel05\GluePHP\Component\AbstractComponent;
use Andaniel05\GluePHP\Tests\StaticTestCase;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class BindAttributesProcessorStaticTest extends StaticTestCase
{
    public function providerGPhpBindAttr()
    {
        return [
            ['gphp-bind-attr'], ['data-gphp-bind-attr']
        ];
    }

    /**
     * @dataProvider providerGPhpBindAttr
     */
    public function testBoundsAttributesAreInitialized($gphpBindAttr)
    {
        $htmlAttrToBind = uniqid('attr');

        $component1 = new class($gphpBindAttr, $htmlAttrToBind) extends AbstractComponent {

            /**
             * @Glue
             */
            protected $gAttr;

            protected $attribute;

            public function __construct($gphpBindAttr, $htmlAttrToBind)
            {
                parent::__construct('component1');

                $this->attribute = $gphpBindAttr.'-'.$htmlAttrToBind;
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
            $this->script("return document.getElementById('div').getAttribute('{$htmlAttrToBind}');")
        );
    }

    /**
     * @dataProvider providerGPhpBindAttr
     */
    public function testAttributeBindingFromTheComponentModelToTheView($gphpBindAttr)
    {
        $htmlAttrToBind = uniqid('attr');

        $component1 = new class($gphpBindAttr, $htmlAttrToBind) extends AbstractComponent {

            /**
             * @Glue
             */
            protected $gAttr;

            protected $attribute;

            public function __construct($gphpBindAttr, $htmlAttrToBind)
            {
                parent::__construct('component1');

                $this->attribute = $gphpBindAttr.'-'.$htmlAttrToBind;
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
            $this->script("return document.getElementById('div').getAttribute('{$htmlAttrToBind}');")
        );
    }

    // /**
    //  * @dataProvider providerGPhpBindAttr
    //  */
    // public function testAttributeBindingFromTheViewToTheComponentModel($gphpBindAttr)
    // {
    //     $htmlAttrToBind = uniqid('attr');

    //     $component1 = new class($gphpBindAttr, $htmlAttrToBind) extends AbstractComponent {

    //         /**
    //          * @Glue
    //          */
    //         protected $gAttr;

    //         protected $attribute;

    //         public function __construct($gphpBindAttr, $htmlAttrToBind)
    //         {
    //             parent::__construct('component1');

    //             $this->attribute = $gphpBindAttr.'-'.$htmlAttrToBind;
    //         }

    //         public function html(): ?string
    //         {
    //             return "<div id=\"div\" {$this->attribute}=\"gAttr\"></div>";
    //         }
    //     };

    //     $this->body->addChild($component1);
    //     $this->writeDocument($this->app->html());

    //     $secret = uniqid();
    //     $this->script("document.getElementById('div').setAttribute('{$htmlAttrToBind}', '{$secret}');");

    //     $this->assertEquals(
    //         $secret, $this->script("return app.getComponent('component1').model.gAttr;")
    //     );
    // }
}
