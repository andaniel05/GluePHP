<?php

namespace Andaniel05\GluePHP\Processor;

class DefaultProcessor extends AbstractProcessor
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

    /////////////////
    // Bind Events //
    /////////////////

    bindEvents('gphp-event');
    bindEvents('data-gphp-event');

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

    /////////////////////
    // Bind Attributes //
    /////////////////////

    bindAttributes('gphp-bind-attr-');
    bindAttributes('data-gphp-bind-attr-');

    function bindAttributes(gphpAttr) {
        traverseElements(function(child) {

            var atts = child.getAttributeNames();
            atts.forEach(function(attr) {

                if (0 === attr.indexOf(gphpAttr)) {

                    var gAttr = child.getAttribute(attr),
                        htmlAttr = attr.substr(gphpAttr.length);

                    child.setAttribute(htmlAttr, component.model[gAttr]);

                    var setterName = GluePHP.Helpers.getSetter(gAttr);
                    var oldSetter = component[setterName];

                    component[setterName] = function(value, registerUpdate = true) {
                        oldSetter.call(this, value, registerUpdate);
                        if (value != child.getAttribute(htmlAttr)) {
                            child.setAttribute(htmlAttr, value);
                        }
                    }

                    var observer = new MutationObserver(function(mutations) {
                        mutations.forEach(function(mutation) {
                            if ('attributes' === mutation.type &&
                                htmlAttr == mutation.attributeName)
                            {
                                var newVal = child.getAttribute(htmlAttr);
                                component[setterName](newVal);
                            }
                        });
                    });

                    var config = {
                        attributes: true,
                        // attributeOldValue: true,
                        attributeFilter: [htmlAttr]
                    };

                    observer.observe(child, config);
                }

            });

        });
    };

    ////////////////////
    // Bind InnerHTML //
    ////////////////////

    bindTextContent('gphp-bind-html');
    bindTextContent('data-gphp-bind-html');

    function bindTextContent(attribute) {
        traverseElements(function(child) {
            if (child.hasAttribute(attribute)) {

                var gAttr = child.getAttribute(attribute);
                child.innerHTML = component.model[gAttr];

                var setterName = GluePHP.Helpers.getSetter(gAttr);
                var oldSetter = component[setterName];

                component[setterName] = function(value, registerUpdate = true) {
                    oldSetter.call(this, value, registerUpdate);
                    child.innerHTML = value;
                }

                var observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if ('childList' === mutation.type &&
                            component.model.gAttr != child.innerHTML)
                        {
                            component[setterName](child.innerHTML);
                        }
                    });
                });

                var config = {
                    childList: true,
                };

                observer.observe(child, config);

            }
        });
    };

JAVASCRIPT;
    }
}
