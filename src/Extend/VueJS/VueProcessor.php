<?php

namespace Andaniel05\GluePHP\Extend\VueJS;

use Andaniel05\ComposedViews\Asset\ScriptAsset;
use Andaniel05\GluePHP\Processor\AbstractProcessor;

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

    component.vueInstances = [];

    // Se tiene que clonar el modelo para que funcione el binding.
    var newModel = {};
    for (var prop in component.model) {
        newModel[prop] = component.model[prop];
    }

    traverseElements(function(element) {
        var vueInstance = new Vue({el: element, data: newModel});
        component.vueInstances.push(vueInstance);
    });

    component.model = newModel;

JAVASCRIPT;
    }
}
