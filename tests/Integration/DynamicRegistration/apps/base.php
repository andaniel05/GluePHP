<?php

use PlatformPHP\GlueApps\Tests\TestApp;
use PlatformPHP\GlueApps\Tests\Integration\Entities\Components\Button;

$button = new Button('button');

$app = new TestApp();
$app->appendComponent('body', $button);
