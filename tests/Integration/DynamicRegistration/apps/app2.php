<?php
/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */

require_once 'base.php';

use Andaniel05\GluePHP\Tests\Integration\Entities\Components\TextInput;

$button->on('click', function ($event) {
    $app = $event->getApp();
    $app->registerComponentClass(TextInput::class, 'input');
});

return $app;
