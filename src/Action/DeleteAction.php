<?php

namespace Andaniel05\GluePHP\Action;

use Andaniel05\GluePHP\AbstractApp;
use Andaniel05\GluePHP\Action\AbstractAction;
use Andaniel05\GluePHP\Component\AbstractComponent;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class DeleteAction extends AbstractAction
{
    public function __construct(AbstractApp $app, AbstractComponent $parent, AbstractComponent $child, bool $render = true)
    {
        parent::__construct([
            'parentId' => $parent->getId(),
            'childId'  => $child->getId(),
        ]);
    }

    public static function handlerScript(): string
    {
        return <<<JAVASCRIPT

    if (app.existsComponent(data.childId)) {
        app.dropComponent(data.childId);
    }

    var parent = app.getComponent(data.parentId);
    if (parent instanceof GluePHP.Component) {
        parent.dropComponent(data.childId);
    }

JAVASCRIPT;
    }
}
