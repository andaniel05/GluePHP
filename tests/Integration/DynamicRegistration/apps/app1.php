<?php

require_once 'base.php';

use Andaniel05\GluePHP\Action\AlertAction;

$button->on('click', function ($event) {
    $app = $event->getApp();
    $app->registerActionClass(AlertAction::class, 'alert');
});

return $app;
