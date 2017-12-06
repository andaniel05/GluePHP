<?php

namespace Andaniel05\GluePHP\Component\Model\Exception;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class InvalidComponentClassException extends ModelException
{
    public function __construct(string $class)
    {
        parent::__construct(
            "La clase \"$class\" no es hija de \"Andaniel05\GluePHP\Component\AbstractComponent\"."
        );
    }
}
