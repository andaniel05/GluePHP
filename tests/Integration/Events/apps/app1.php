<?php
/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */

use Andaniel05\GluePHP\Tests\TestApp;
use Andaniel05\GluePHP\Tests\Integration\Entities\Components\TextInput;
use Andaniel05\GluePHP\Action\AlertAction;

$callback = function ($event) {
    $eventData = $event->getData();
    $msg = $eventData['key'] . $eventData['charCode'];
    $event->app->act(new AlertAction($msg));
};

$input = new TextInput('input');
$app = new TestApp();
$app->appendComponent('body', $input);

$input->on('keypress', $callback, ['key', 'charCode']);

return $app;
