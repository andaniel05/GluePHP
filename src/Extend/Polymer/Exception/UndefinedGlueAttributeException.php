<?php

namespace Andaniel05\GluePHP\Extend\Polymer\Exception;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class UndefinedGlueAttributeException extends \Exception
{
    public function __construct(string $attribute)
    {
        parent::__construct("The component has not none glue attribute with name equal to '{$attribute}'");
    }
}
