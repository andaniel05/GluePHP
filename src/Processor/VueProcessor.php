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

    // Se tiene que clonar el modelo para que funcione el binding.
    var newModel = {};
    for (var prop in component.model) {
        newModel[prop] = component.model[prop];
    }

    // component.vueInstance = new Vue({
    //     el: component.element,
    //     data: newModel
    // });

    // component.model = newModel;

    ///////////////
    // Con Hijos //
    ///////////////

    traverseElements(function(element) {
        new Vue({el: element, data: newModel});
    });

    component.model = newModel;

JAVASCRIPT;
    }
}
