<?php
/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */

require_once 'base.php';

use Andaniel05\GluePHP\Tests\Integration\Entities\Components\Button;
use Andaniel05\GluePHP\Action\AlertAction;

$button1->on('click', function ($event1) {
    $button2 = new Button('button2');
    $button2->on('click', function ($event2) {
        $action = new AlertAction('button2.click');
        $app = $event2->getApp();
        $app->act($action);
    });

    $app = $event1->getApp();
    $app->appendComponent('body', $button2);
});

return $app;
