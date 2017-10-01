<?php

use PlatformPHP\GlueApps\Tests\TestApp;
use PlatformPHP\GlueApps\Tests\Integration\Entities\Components\{TextInput, Button};

$input1 = new TextInput('input1');
$input2 = new TextInput('input2');
$button = new Button('button');

$app = new TestApp();
$app->appendComponent('body', $input1);
$app->appendComponent('body', $input2);
$app->appendComponent('body', $button);
