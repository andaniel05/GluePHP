<?php

require_once 'base.php';

$button->on('click', function ($event) {

    $processor = new class extends Andaniel05\GluePHP\Processor\AbstractProcessor {

        public static function script(): string
        {
            return <<<JAVASCRIPT
    component.secret = 'secret';
JAVASCRIPT;
        }
    };

    $app = $event->getApp();
    $app->registerProcessorClass(get_class($processor), 'processor');
});

return $app;
