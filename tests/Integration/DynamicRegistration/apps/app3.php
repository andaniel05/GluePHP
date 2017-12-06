<?php
/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */

require_once 'base.php';

use Andaniel05\GluePHP\Action\AlertAction;

$button->on('click', function ($event) {
    $app = $event->getApp();
    $action = new AlertAction('secret');
    $app->act($action);
});

return $app;
