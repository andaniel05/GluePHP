<?php

require_once 'base.php';

use Andaniel05\GluePHP\Tests\Integration\Entities\Actions\AlertAction;

$button->on('click', function ($event) {
    $app = $event->getApp();
    $action = new AlertAction('secret');
    $app->act($action);
});

return $app;
