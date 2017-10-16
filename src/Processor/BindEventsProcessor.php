<?php

namespace Andaniel05\GluePHP\Processor;

class BindEventsProcessor extends AbstractProcessor
{
    public static function script(): string
    {
        return <<<JAVASCRIPT
    var bindEvents = function(attribute) {

        if ( ! (component.element instanceof Element)) {
            return;
        }

        var traverse = function(element) {

            for (var child of element.children) {

                if (child.classList.contains(component.childrenClass)) {
                    continue;
                }

                if (child.hasAttribute(attribute)) {
                    var events = child.getAttribute(attribute).split(' ');
                    events.forEach(function (eventName) {
                        child.addEventListener(eventName, function(event) {
                            component.dispatch(eventName, event);
                        });
                    });
                }

                traverse(child);
            }
        };

        traverse(component.element);
    };

    bindEvents('g-event');
    bindEvents('data-g-event');

JAVASCRIPT;
    }
}
