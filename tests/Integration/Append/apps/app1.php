<?php

require_once 'base.php';

use Andaniel05\GluePHP\Tests\Integration\Entities\Components\Button;

$button1->on('click', function ($event) {
    $app = $event->getApp();
    $button2 = new Button('button2');
    $app->appendComponent('body', $button2);
});

return $app;
