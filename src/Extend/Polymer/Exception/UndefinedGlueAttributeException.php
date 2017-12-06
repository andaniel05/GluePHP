<?php

namespace Andaniel05\GluePHP\Extend\Polymer\Exception;

class UndefinedGlueAttributeException extends \Exception
{
    public function __construct(string $attribute)
    {
        parent::__construct("The component has not none glue attribute with name equal to '{$attribute}'");
    }
}
