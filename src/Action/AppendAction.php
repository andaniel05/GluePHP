<?php

namespace Andaniel05\GluePHP\Action;

use Andaniel05\GluePHP\Action\AbstractAction;
use Andaniel05\ComposedViews\Component\AbstractComponent;

class AppendAction extends AbstractAction
{
    public function __construct(AbstractComponent $parent, AbstractComponent $child)
    {
        parent::__construct([
            'parentId' => $parent->getId(),
            'childId'  => $child->getId(),
            'html'     => $child->html(),
        ]);
    }

    public static function handlerScript(): string
    {
        return <<<JAVASCRIPT
    var parent = app.getComponent(data.parentId);

    if (parent instanceof GluePHP.Component &&
        parent.html instanceof Element)
    {
        var childElement = document.createElement('div');
        childElement.innerHTML = data.html;
        childElement = childElement.firstChild;

        parent.html.append(childElement);
    }

JAVASCRIPT;
    }
}
