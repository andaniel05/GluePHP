<?php

namespace Andaniel05\GluePHP\Processor;

class BindDataProcessor extends AbstractProcessor
{
    public static function script(): string
    {
        return <<<JAVASCRIPT

    if (component.element instanceof Element) {
        bindData('g-bind');
        bindData('data-g-bind');
    }

    function bindData(attribute) {

        var traverse = function(element) {

            for (var child of element.children) {

                if (child.classList.contains(component.childrenClass)) {
                    continue;
                }

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

                traverse(child);
            }
        };

        traverse(component.element);
    };

JAVASCRIPT;
    }
}
