<?php

namespace Andaniel05\GluePHP\Processor;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class BindHtmlProcessor extends AbstractProcessor
{
    public static function script(): string
    {
        return <<<JAVASCRIPT

    if ( ! component.element instanceof Element) {
        return;
    }

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

                // var observer = new MutationObserver(function(mutations) {
                //     mutations.forEach(function(mutation) {
                //         if ('childList' === mutation.type &&
                //             component.model.gAttr != child.innerHTML)
                //         {
                //             component[setterName](child.innerHTML);
                //         }
                //     });
                // });

                // var config = {
                //     childList: true,
                // };

                // observer.observe(child, config);

            }
        });
    };

JAVASCRIPT;
    }
}
