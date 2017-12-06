<?php

namespace Andaniel05\GluePHP;

use Andaniel05\GluePHP\Component\Model\Exception\InvalidTypeException;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
function jsVal($value)
{
    $type = gettype($value);
    $strVal = '';

    switch ($type) {

        case 'string':
            $strVal = "'{$value}'";
            break;

        case 'integer':
            $strVal = strval($value);
            break;

        case 'double':
            $strVal = strval($value);
            break;

        case 'boolean':
            $strVal = $value ? 'true' : 'false';
            break;

        case 'NULL':
            $strVal = 'null';
            break;

        case 'array':
        case 'object':

            if (is_callable($value)) {
                throw new InvalidTypeException($type);
            }

            $strVal = json_encode($value);
            break;

        default:
            throw new InvalidTypeException($type);
    }

    return $strVal;
}
