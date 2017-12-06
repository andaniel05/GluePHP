<?php

namespace Andaniel05\GluePHP\Extend\Polymer;

use Andaniel05\GluePHP\Processor\AbstractProcessor;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class WebComponentProcessor extends AbstractProcessor
{
    public static function script(): string
    {
        return <<<JAVASCRIPT

    var customElement = component.customElement = component.element.children[0];

    component._bindEvents.forEach(function(eventName) {
        component.element.addEventListener(eventName, function(event) {
            component.dispatch(eventName, event);
        });
    });

    for (var gAttr in component._bindProperties) {
        var prop = component._bindProperties[gAttr];

        customElement[prop] = component.model[gAttr];

        var setterName = GluePHP.Helpers.getSetter(gAttr);
        var oldSetter = component[setterName];
        component[setterName] = function(value, registerUpdate = true, assignToCustomElement = true) {
            oldSetter.call(this, value, registerUpdate);
            if (assignToCustomElement === true) {
                customElement[prop] = value;
            }
        };

        var oldPropSetter = customElement.__lookupSetter__(prop);
        var propGetter = customElement.__lookupGetter__(prop);
        Object.defineProperty(customElement, prop, {
            set: function(newVal) {
                component[setterName](newVal, true, false);
                oldPropSetter.call(this, newVal);
            },
            get: propGetter
        });
    }

JAVASCRIPT;
    }
}
