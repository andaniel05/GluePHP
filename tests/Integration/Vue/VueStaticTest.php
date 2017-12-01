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
    }

    public function testVueJsAssetIsRegistered()
    {
        $this->loadApp1();

        $this->assertStringStartsWith('2.', $this->script("return Vue.version"));
    }

    public function testVueInstanceHasElementEquivalentToTheComponentElement()
    {
        $this->loadApp1();

        $script = "
            button1 = app.getComponent('button1');
            return button1.element.getAttribute('id') === button1.vueInstance.\$el.getAttribute('id');
        ";

        $this->assertTrue($this->script($script));
    }

    public function testVueInstanceHasDataEqualToComponentModel()
    {
        $this->loadApp1();

        $script = "
            button1 = app.getComponent('button1');
            return button1.model == button1.vueInstance.\$data;
        ";

        $this->assertTrue($this->script($script));
    }

    public function testDataBindigIsInitialized()
    {
        $this->loadApp1();

        $this->assertEquals($this->text, $this->script("return document.querySelector('button').textContent;"));
    }
}
