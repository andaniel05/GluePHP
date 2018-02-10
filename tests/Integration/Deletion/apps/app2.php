<?php
/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */

require_once 'base.php';

use Andaniel05\GluePHP\Tests\Integration\Entities\Components\TextInput;

$button1->on('click', function ($event) {
    $input = new TextInput('input');
    $event->app->appendComponent('body', $input);
});

$button2->on('click', function ($event) {
    $event->app->input->detach();
});

return $app;
