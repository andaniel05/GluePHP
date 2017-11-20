<?php

require_once 'base.php';

use Andaniel05\GluePHP\Tests\Integration\Entities\Components\Button;
use Andaniel05\GluePHP\Action\AlertAction;
use Andaniel05\GluePHP\Component\Sidebar;

$button1->on('click', function ($event1) {

    $button2 = new Button('button2');
    $button2->on('click', function ($event2) {
        $action = new AlertAction('button2.click');
        $app = $event2->getApp();
        $app->act($action);
    });

    $sidebar = new Sidebar('sidebar');
    $sidebar->addChild($button2);

    $app = $event1->getApp();
    $app->appendComponent('body', $sidebar);
});

return $app;
