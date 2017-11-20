<?php

namespace Andaniel05\GluePHP\Processor;

abstract class AbstractProcessor
{
    public static function assets(): array
    {
        return [];
    }

    final public static function scriptWrapper(): string
    {
        $script = static::script();

        return <<<JAVASCRIPT
function(component) {

{$script}

    function traverseElements(callback, includeChildren = false) {

        if ( ! (component.element instanceof Element)) {
            return;
        }

        var traverse = function(element) {

            for (var child of element.children) {

                if (false === includeChildren &&
                    child.classList.contains(component.childrenClass))
                {
                    continue;
                }

                callback(child);

                traverse(child);
            }
        };

        traverse(component.element);
    };
}
JAVASCRIPT;
    }

    abstract public static function script(): string;
}
