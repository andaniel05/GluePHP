<?php

namespace Andaniel05\GluePHP\Action;

use Andaniel05\GluePHP\Action\AbstractAction;
use Andaniel05\GluePHP\Component\AbstractComponent;

class AppendAction extends AbstractAction
{
    public function __construct(AbstractComponent $parent, AbstractComponent $child)
    {
        $html = AbstractComponent::containerView(
            $child->getId(), $child->html()
        );

        parent::__construct([
            'parentId' => $parent->getId(),
            'childId'  => $child->getId(),
            'html'     => $html,
        ]);
    }

    public static function handlerScript(): string
    {
        return <<<JAVASCRIPT
    var childElement = document.createElement('div');
    childElement.innerHTML = data.html;
    childElement = childElement.firstChild;

    var parent = app.getComponent(data.parentId);
    parent.childrenElement.append(childElement);

    var child = new GluePHP.Component(data.childId, app);
    parent.addComponent(child);

JAVASCRIPT;
    }
}
