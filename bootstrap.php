<?php

define('ROOT_DIR', __DIR__);

require_once ROOT_DIR . '/vendor/autoload.php';

use Andaniel05\GluePHP\Component\AbstractComponent;

function appUri(string $appFileName, array $args = [], $saveInSession = true): string
{
    $query_data = array_merge([
        'app'           => $appFileName,
        'saveInSession' => $saveInSession,
    ], $args);

    $query = http_build_query($query_data);

    return $GLOBALS['test_server'] . "tests/load.php?$query";
}

function controllerUri(): string
{
    $testServer = $GLOBALS['test_server'] ?? 'http://localhost:8085/';

    return "$testServer/tests/controller.php";
}

function importUri(string $file): string
{
    $testServer = $GLOBALS['test_server'] ?? 'http://localhost:8085/';

    return "{$testServer}tests/{$file}";
}

function getDummyComponent(string $id): AbstractComponent
{
    return new class($id) extends AbstractComponent {};
}

function frand($min = 0, $max = 10)
{
    return ($min + lcg_value() * (abs($max - $min)));
}

function setAttr($value, $attribute, $object)
{
    $closure = function () use ($value, $attribute) {
        $this->{$attribute} = $value;
    };

    $closure->call($object);
}
