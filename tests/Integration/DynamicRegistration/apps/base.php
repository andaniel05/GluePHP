<?php

use Andaniel05\GluePHP\Tests\TestApp;
use Andaniel05\GluePHP\Tests\Integration\Entities\Components\Button;

$button = new Button('button');

$app = new TestApp();
$app->appendComponent('body', $button);
