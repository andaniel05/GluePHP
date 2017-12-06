<?php

namespace Andaniel05\GluePHP\Component\Model\Exception;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class InvalidTypeException extends ModelException
{
    public function __construct(string $type)
    {
        parent::__construct(
            "El tipo '$type' no es soportado."
        );
    }
}
