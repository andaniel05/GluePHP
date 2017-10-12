<?php

require_once 'base.php';

use Andaniel05\GluePHP\Tests\Integration\Entities\Actions\AlertAction;

$button->on('click', function ($event) {
    $app = $event->getApp();
    $app->registerActionClass(AlertAction::class, 'alert');
});

return $app;
