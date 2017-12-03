<?php

namespace Andaniel05\GluePHP\Processor;

class ShortEventsProcessor extends AbstractProcessor
{
    public static function script(): string
    {
        return <<<JAVASCRIPT

    if ( ! component.element instanceof Element) {
        return;
    }

    //////////////////
    // Short Events //
    //////////////////

    traverseElements(function(element) {
        var attributes = element.getAttributeNames();
        attributes.forEach(function(attribute) {
            if (0 === attribute.indexOf('@@')) {
                var eventName = attribute.substr(2);
                element.addEventListener(eventName, function(event) {
                    component.dispatch(eventName, event);
                });
            }
        });
    });

JAVASCRIPT;
    }
}
