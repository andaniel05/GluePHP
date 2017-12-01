<?php

namespace Andaniel05\GluePHP\Processor;

use Andaniel05\ComposedViews\Asset\ScriptAsset;

class VueProcessor extends AbstractProcessor
{
    public static function assets(): array
    {
        return [
            'vuejs' => new ScriptAsset('vuejs', '')
        ];
    }

    public static function script(): string
    {
        return <<<JAVASCRIPT
    component.vueInstance = new Vue({
        el: component.element,
        data: component.model
    });
JAVASCRIPT;
    }
}
