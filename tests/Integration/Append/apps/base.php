<?php

use Andaniel05\GluePHP\Tests\TestApp;
use Andaniel05\GluePHP\Tests\Integration\Entities\Components\Button;

$button1 = new Button('button1');

$app = new TestApp();
$app->appendComponent('body', $button1);
