<?php

define('ROOT_DIR', __DIR__);

require_once ROOT_DIR . '/vendor/autoload.php';

use PlatformPHP\GlueApps\Component\AbstractComponent;

function appUrl(string $appFileName, array $args = [], $saveInSession = true): string
{
    $query_data = array_merge([
        'app'           => $appFileName,
        'saveInSession' => $saveInSession,
    ], $args);

    $query = http_build_query($query_data);

    return $GLOBALS['test_server'] . "tests/load.php?$query";
}

function controllerUrl(): string
{
    $testServer = $GLOBALS['test_server'] ?? 'http://localhost:8085/';

    return "$testServer/tests/controller.php";
}

function getDummyComponent(string $id): AbstractComponent
{
    return new class($id) extends AbstractComponent {};
}

function frand($min = 0, $max = 10)
{
    return ($min + lcg_value() * (abs($max - $min)));
}
