<?php

require_once 'base.php';

$button1->on('click', function ($event) {
    $app = $event->getApp();
    $body = $app->getComponent('body');
    $body->dropChild('button2');
});

return $app;
