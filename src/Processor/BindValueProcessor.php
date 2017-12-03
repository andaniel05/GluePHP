<?php

namespace Andaniel05\GluePHP\Processor;

class BindValueProcessor extends AbstractProcessor
{
    public static function script(): string
    {
        return <<<JAVASCRIPT

    if ( ! component.element instanceof Element) {
        return;
    }

    ////////////////
    // Bind Value //
    ////////////////

    bindValue('gphp-bind-value');
    bindValue('data-gphp-bind-value');

    function bindValue(attribute) {
        traverseElements(function(child) {

            if (child.hasAttribute(attribute)) {

                var modelAttribute = child.getAttribute(attribute);
                var setterName = GluePHP.Helpers.getSetter(modelAttribute);

                component.model[modelAttribute] = child.value;

                child.onchange = function(event) {
                    component[setterName](child.value);
                };

                var oldSetter = component[setterName];
                component[setterName] = function(value, registerUpdate = true) {
                    oldSetter.call(this, value, registerUpdate);
                    child.value = value;
                }
            }
        });
    };

JAVASCRIPT;
    }
}
