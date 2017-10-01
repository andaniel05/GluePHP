<?php

namespace PlatformPHP\GlueApps\Component\Model\Exception;

class InvalidTypeException extends ModelException
{
    public function __construct(string $type)
    {
        parent::__construct(
            "El tipo '$type' no es soportado."
        );
    }
}