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
        traverseElements(function(child) {
            if (child.hasAttribute(attribute)) {
                var events = child.getAttribute(attribute).split(' ');
                events.forEach(function (eventName) {
                    child.addEventListener(eventName, function(event) {
                        component.dispatch(eventName, event);
                    });
                });
            }
        });
    };

JAVASCRIPT;
    }
}
