<?php

namespace Andaniel05\GluePHP\Processor;

class BindEventsProcessor extends AbstractProcessor
{
    public static function script(): string
    {
        return <<<JAVASCRIPT

    if (component.element instanceof Element) {
        bindEvents('g-event');
        bindEvents('data-g-event');
    }

    function bindEvents(attribute) {

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

JAVASCRIPT;
    }
}
