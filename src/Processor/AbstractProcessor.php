<?php

namespace Andaniel05\GluePHP\Processor;

abstract class AbstractProcessor
{
    final public static function scriptWrapper(): string
    {
        $script = static::script();

        return <<<JAVASCRIPT
function(component) {
    {$script}
}
JAVASCRIPT;
    }

    abstract public static function script(): string;
}
