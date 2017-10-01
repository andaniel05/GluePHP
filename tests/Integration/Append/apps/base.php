<?php

use PlatformPHP\GlueApps\Tests\TestApp;
use PlatformPHP\GlueApps\Tests\Integration\Entities\Components\Button;

$button1 = new Button('button1');

$app = new TestApp();
$app->appendComponent('body', $button1);
