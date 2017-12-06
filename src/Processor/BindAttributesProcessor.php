<?php

namespace Andaniel05\GluePHP\Processor;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class BindAttributesProcessor extends AbstractProcessor
{
    public static function script(): string
    {
        return <<<JAVASCRIPT

    if ( ! component.element instanceof Element) {
        return;
    }

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

                    // var observer = new MutationObserver(function(mutations) {
                    //     mutations.forEach(function(mutation) {
                    //         if ('attributes' === mutation.type &&
                    //             htmlAttr == mutation.attributeName)
                    //         {
                    //             var newVal = child.getAttribute(htmlAttr);
                    //             component[setterName](newVal);
                    //         }
                    //     });
                    // });

                    // var config = {
                    //     attributes: true,
                    //     // attributeOldValue: true,
                    //     attributeFilter: [htmlAttr]
                    // };

                    // observer.observe(child, config);
                }

            });

        });
    };

JAVASCRIPT;
    }
}
