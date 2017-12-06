<?php

namespace Andaniel05\GluePHP\Tests\Integration\Extend\VueJS;

use Andaniel05\GluePHP\Tests\StaticTestCase;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class VueStaticTest extends StaticTestCase
{
    public function loadApp1()
    {
        $this->text = uniqid();
        $this->driver->get(
            appUri(__DIR__ . '/apps/app1.php', ['text' => $this->text])
        );

        $this->script("button = app.getComponent('button');");
    }

    public function loadApp2()
    {
        $this->buttonText = uniqid();
        $this->groupText = uniqid();

        $this->driver->get(
            appUri(__DIR__ . '/apps/app2.php', [
                'buttonText' => $this->buttonText,
                'groupText'  => $this->groupText,
            ])
        );

        $script = "
            button = app.getComponent('button');
            group = app.getComponent('group');
        ";

        $this->script($script);
    }

    public function testVueJsAssetIsRegistered()
    {
        $this->loadApp1();

        $this->assertStringStartsWith('2.', $this->script("return Vue.version"));
    }

    public function testDataBindigIsInitialized()
    {
        $this->loadApp1();

        $this->assertEquals($this->text, $this->script("return document.querySelector('button').textContent;"));
    }

    public function testBindingFromModelToViewAcrossSetter()
    {
        $this->loadApp1();

        $text = uniqid('changed');
        $this->script("button.setText('{$text}')");

        $this->assertEquals($text, $this->script("return button.model.text"));
        $this->assertEquals($text, $this->script("return document.querySelector('button').textContent;"));
    }

    public function testBindingFromModelToViewAcrossProperty()
    {
        $this->loadApp1();

        $text = uniqid('changed');
        $this->script("button.model.text = '{$text}';");

        $this->assertEquals($text, $this->script("return button.model.text"));
        $this->assertEquals($text, $this->script("return document.querySelector('button').textContent;"));
    }

    public function testInitializedDataBindigOnNestedComponents()
    {
        $this->loadApp2();

        $this->assertEquals(
            $this->groupText,
            $this->script("return document.querySelector('label').textContent;")
        );
        $this->assertEquals(
            $this->buttonText,
            $this->script("return document.querySelector('button').textContent;")
        );
    }

    public function testBindingFromModelToViewAcrossSetterOnNestedComponents()
    {
        $this->loadApp2();

        $buttonText = uniqid('changed');
        $this->script("button.setText('{$buttonText}')");

        $groupText = uniqid('changed');
        $this->script("group.setText('{$groupText}')");

        $this->assertEquals($groupText, $this->script("return group.model.text"));
        $this->assertEquals($groupText, $this->script("return document.querySelector('label').textContent;"));
        $this->assertEquals($buttonText, $this->script("return button.model.text"));
        $this->assertEquals($buttonText, $this->script("return document.querySelector('button').textContent;"));
    }

    public function testBindingFromModelToViewAcrossPropertyOnNestedComponents()
    {
        $this->loadApp2();

        $buttonText = uniqid('changed');
        $this->script("button.model.text = '{$buttonText}';");

        $groupText = uniqid('changed');
        $this->script("group.model.text = '{$groupText}';");

        $this->assertEquals($groupText, $this->script("return group.model.text"));
        $this->assertEquals($groupText, $this->script("return document.querySelector('label').textContent;"));
        $this->assertEquals($buttonText, $this->script("return button.model.text"));
        $this->assertEquals($buttonText, $this->script("return document.querySelector('button').textContent;"));
    }
}
