<?php
/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */

use Andaniel05\GluePHP\Tests\TestApp;
use Andaniel05\GluePHP\Tests\Integration\Entities\Components\Button;

$button1 = new Button('button1');
$button2 = new Button('button2');

$app = new TestApp();
$app->appendComponent('body', $button1);
$app->appendComponent('body', $button2);
