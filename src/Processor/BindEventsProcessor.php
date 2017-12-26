<?php

namespace Andaniel05\GluePHP\Processor;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class BindEventsProcessor extends AbstractProcessor
{
    public static function script(): string
    {
        return <<<JAVASCRIPT

    if ( ! component.element instanceof Element) {
        return;
    }

    /////////////////
    // Bind Events //
    /////////////////

    bindEvents('gphp-bind-events');
    bindEvents('data-gphp-bind-events');

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
