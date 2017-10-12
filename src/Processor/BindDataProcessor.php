<?php

namespace Andaniel05\GluePHP\Processor;

class BindDataProcessor extends AbstractProcessor
{
    public static function script(): string
    {
        return <<<JAVASCRIPT
    var bindData = function(attribute) {

        if ( ! (component.html instanceof Element)) {
            return;
        }

        var items = component.html.querySelectorAll('*[' + attribute + ']');
        items.forEach(function(item) {

            var modelAttribute = item.getAttribute(attribute);
            var setterName = GluePHP.Helpers.getSetter(modelAttribute);

            component.model[modelAttribute] = item.value;

            item.onchange = function(event) {
                component[setterName](item.value);
            };

            var oldSetter = component[setterName];
            component[setterName] = function(value, registerUpdate = true) {
                oldSetter.call(this, value, registerUpdate);
                item.value = value;
            }
        });
    };

    bindData('g-bind');
    bindData('data-g-bind');
JAVASCRIPT;
    }
}
