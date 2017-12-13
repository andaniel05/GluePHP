<?php
/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */

use Andaniel05\GluePHP\Tests\Unit\Extended\Polymer\TestApp;
use Andaniel05\GluePHP\Tests\Integration\Entities\Components\CustomElement;

$secret = $_GET['secret'];

$component = new CustomElement('component');
$component->setText($secret);

$app = new TestApp();
$app->appendComponent('body', $component);

return $app;
