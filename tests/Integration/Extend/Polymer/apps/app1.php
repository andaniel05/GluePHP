<?php
/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */

use Andaniel05\GluePHP\Tests\Unit\Extend\Polymer\TestApp;
use Andaniel05\GluePHP\Extend\Polymer\WebComponent;
use Andaniel05\GluePHP\Action\AlertAction;

$eventName = $_GET['eventName'];

$component = new WebComponent('component', 'my-tag', '');
setAttr([$eventName], 'bindEvents', $component);
$component->on($eventName, function ($ev) use ($eventName) {
    $ev->app->act(new AlertAction($eventName));
});

$app = new TestApp();
$app->appendComponent('body', $component);

return $app;
