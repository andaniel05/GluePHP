<?php

namespace Andaniel05\GluePHP\Action;

use Andaniel05\GluePHP\AbstractApp;
use Andaniel05\GluePHP\Action\AbstractAction;
use Andaniel05\GluePHP\Component\AbstractComponent;

class DeleteAction extends AbstractAction
{
    public function __construct(AbstractApp $app, AbstractComponent $parent, AbstractComponent $child, bool $render = true)
    {
        parent::__construct([
            // 'parentId' => $parent->getId(),
            'childId'  => $child->getId(),
        ]);
    }

    public static function handlerScript(): string
    {
        return <<<JAVASCRIPT
    // var parent = app.getComponent(data.parentId);
    // parent.dropComponent(data.childId);
    app.dropComponent(data.childId);
JAVASCRIPT;
    }
}
