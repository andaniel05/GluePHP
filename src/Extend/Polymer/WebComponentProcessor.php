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

    if ("object" === typeof WebComponents && true === WebComponents.ready) {
        exec();
    } else {
        window.addEventListener('WebComponentsReady', exec);
    }

    function exec() {
        let customElement = component.customElement = component.element.children[0];

        component._bindEvents.forEach(function(eventName) {
            component.element.addEventListener(eventName, function(event) {
                component.dispatch(eventName, event);
            });
        });

        for (let gAttr in component._bindProperties) {
            let prop = component._bindProperties[gAttr];

            customElement[prop] = component.model[gAttr];

            let setterName = GluePHP.Helpers.getSetter(gAttr);
            let oldSetter = component[setterName];
            component[setterName] = function(value, registerUpdate = true, assignToCustomElement = true) {
                oldSetter.call(this, value, registerUpdate);
                if (assignToCustomElement === true) {
                    customElement[prop] = value;
                }
            };

            let oldPropSetter = customElement.__lookupSetter__(prop);
            let propGetter = customElement.__lookupGetter__(prop);
            Object.defineProperty(customElement, prop, {
                set: function(newVal) {
                    component[setterName](newVal, true, false);
                    oldPropSetter.call(this, newVal);
                },
                get: propGetter
            });
        }
    };

JAVASCRIPT;
    }
}
