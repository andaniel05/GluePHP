<?php

use Andaniel05\GluePHP\Tests\Unit\Extend\Polymer\TestApp;
use Andaniel05\GluePHP\Tests\Integration\Entities\Components\CustomElement;

$component = new CustomElement('component');

$app = new TestApp();
$app->appendComponent('body', $component);

return $app;
