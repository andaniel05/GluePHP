<?php

require_once 'base.php';

use PlatformPHP\GlueApps\Tests\Integration\Entities\Actions\AlertAction;

$button->on('click', function () use ($app) {
    $app->registerActionClass(AlertAction::class, 'alert');
});

return $app;
