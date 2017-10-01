<?php

require_once 'base.php';

$app->setSendActions(false);

$app->on('button.click', function ($event) use ($input1, $input2) {
    $input2->setText($input1->getText());
});

return $app;
