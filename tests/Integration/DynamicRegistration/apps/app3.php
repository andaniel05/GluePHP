<?php

require_once 'base.php';

use PlatformPHP\GlueApps\Tests\Integration\Entities\Actions\AlertAction;

$button->on('click', function () use ($app) {
    $action = new AlertAction('secret');
    $app->act($action);
});

return $app;
