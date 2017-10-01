<?php

namespace PlatformPHP\GlueApps\Component\Model\Exception;

class InvalidComponentClassException extends ModelException
{
    public function __construct(string $class)
    {
        parent::__construct(
            "La clase \"$class\" no es hija de \"PlatformPHP\GlueApps\Component\AbstractComponent\"."
        );
    }
}