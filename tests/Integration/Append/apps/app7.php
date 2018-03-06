<?php
/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */

require_once 'base.php';

use Andaniel05\GluePHP\Tests\Integration\Entities\Components\Button;

$button1->on('click', function ($e) {
    $button1 = new Button('button1');
    $e->app->appendComponent('body', $button1);
});

return $app;
