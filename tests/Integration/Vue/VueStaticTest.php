<?php

namespace Andaniel05\GluePHP\Tests\Integration\Vue;

use Andaniel05\GluePHP\Tests\StaticTestCase;

class VueStaticTest extends StaticTestCase
{
    public function loadApp1()
    {
        $this->text = uniqid();
        $this->driver->get(
            appUri(__DIR__ . '/apps/app1.php', ['text' => $this->text])
        );

        $this->script("button1 = app.getComponent('button1');");
    }

    public function testVueJsAssetIsRegistered()
    {
        $this->loadApp1();

        $this->assertStringStartsWith('2.', $this->script("return Vue.version"));
    }

    public function testVueInstanceHasElementEquivalentToTheComponentElement()
    {
        $this->loadApp1();

        $this->assertTrue($this->script("return button1.element.getAttribute('id') === button1.vueInstance.\$el.getAttribute('id');"));
    }

    public function testVueInstanceHasDataEqualToComponentModel()
    {
        $this->loadApp1();

        $this->assertTrue($this->script("return button1.model == button1.vueInstance.\$data;"));
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
        $this->script("button1.setText('{$text}')");

        $this->assertEquals($text, $this->script("return button1.model.text"));
        $this->assertEquals($text, $this->script("return document.querySelector('button').textContent;"));
    }

    public function testBindingFromModelToViewAcrossProperty()
    {
        $this->loadApp1();

        $text = uniqid('changed');
        $this->script("button1.model.text = '{$text}';");

        $this->assertEquals($text, $this->script("return button1.model.text"));
        $this->assertEquals($text, $this->script("return document.querySelector('button').textContent;"));
    }
}
