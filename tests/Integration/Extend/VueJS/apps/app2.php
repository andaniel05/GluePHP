<?php

use Andaniel05\GluePHP\Tests\TestApp;
use Andaniel05\GluePHP\Tests\Integration\Entities\Components\{VueButton, VueGroup};

$buttonText = $_GET['buttonText'];
$button = new VueButton('button');
$button->setText($buttonText);

$groupText = $_GET['groupText'];
$group = new VueGroup('group');
$group->setText($groupText);
$group->addChild($button);

$app = new TestApp();
$app->appendComponent('body', $group);

return $app;
