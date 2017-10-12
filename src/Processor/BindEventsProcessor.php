<?php

namespace Andaniel05\GluePHP\Processor;

class BindEventsProcessor extends AbstractProcessor
{
    public static function script(): string
    {
        return <<<JAVASCRIPT
    var bindEvents = function(attribute) {

        if ( ! (component.html instanceof Element)) {
            return;
        }

        var items = component.html.querySelectorAll('*[' + attribute + ']');
        items.forEach(function(item) {
            var events = item.getAttribute(attribute).split(' ');
            events.forEach(function (eventName) {
                item.addEventListener(eventName, function(event) {
                    component.dispatch(eventName, event);
                });
            });
        });
    };

    bindEvents('g-event');
    bindEvents('data-g-event');
JAVASCRIPT;
    }
}
