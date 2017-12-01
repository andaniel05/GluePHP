<?php

use Andaniel05\GluePHP\Tests\TestApp;
use Andaniel05\GluePHP\Tests\Integration\Entities\Components\VueButton;

$button1 = new VueButton('button1');

$app = new TestApp();
$app->appendComponent('body', $button1);
