<?php

require_once 'base.php';

use Andaniel05\GluePHP\Tests\Integration\Entities\Actions\AlertAction;

$button->on('click', function () use ($app) {
    $action = new AlertAction('secret');
    $app->act($action);
});

return $app;
