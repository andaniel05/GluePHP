<?php

namespace Andaniel05\GluePHP\Action;

use Andaniel05\GluePHP\Action\AbstractAction;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class RegisterEventAction extends AbstractAction
{
    public function __construct(string $eventName, array $data)
    {
        parent::__construct([
            'eventName' => $eventName,
            // 'data'      => $data,
        ]);
    }

    public static function handlerScript(): string
    {
        return <<<JAVASCRIPT
    console.log(data);
    app.registerEvent(data.eventName);
JAVASCRIPT;
    }
}
