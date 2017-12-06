<?php
/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */

use Andaniel05\GluePHP\Tests\TestApp;
use Andaniel05\GluePHP\Tests\Integration\Entities\Components\TextInput;
use Andaniel05\GluePHP\Tests\Integration\Entities\Components\Button;

$input1 = new TextInput('input1');
$input2 = new TextInput('input2');
$button = new Button('button');

$app = new TestApp();
$app->appendComponent('body', $input1);
$app->appendComponent('body', $input2);
$app->appendComponent('body', $button);
