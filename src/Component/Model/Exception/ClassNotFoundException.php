<?php

namespace PlatformPHP\GlueApps\Component\Model\Exception;

class ClassNotFoundException extends ModelException
{
    public function __construct(string $class)
    {
        parent::__construct(
            "La clase \"$class\" no existe."
        );
    }
}