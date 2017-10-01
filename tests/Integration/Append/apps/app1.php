<?php

require_once 'base.php';

$button1->on('click', function () use ($app) {
    $button2 = new Button('button2');
    $app->appendComponent('body', $button1);
});

return $app;
