<?php
/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */

require_once 'base.php';

$button1->on('click', function ($event) {
    $app = $event->getApp();
    $body = $app->getComponent('body');
    $body->dropChild('button2');
});

return $app;
