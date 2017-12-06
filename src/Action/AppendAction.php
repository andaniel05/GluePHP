<?php

namespace Andaniel05\GluePHP\Action;

use Andaniel05\GluePHP\AbstractApp;
use Andaniel05\GluePHP\Action\AbstractAction;
use Andaniel05\GluePHP\Component\AbstractComponent;
use Andaniel05\GluePHP\Component\Sidebar;
use Andaniel05\GluePHP\Component\Model\Model;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class AppendAction extends AbstractAction
{
    public function __construct(AbstractApp $app, AbstractComponent $parent, AbstractComponent $child, bool $render = true)
    {
        $html = $render ?
            AbstractComponent::containerView(
                $child->getId(),
                $child->html()
            ) : null;

        $html = null;
        if ($render) {
            if ($child instanceof Sidebar) {
                $html = $child->html();
            } else {
                $html = AbstractComponent::containerView(
                    $child->getId(),
                    $child->html()
                );
            }
        }

        $frontProcessors = [];
        foreach ($child->processors() as $class) {
            if (! $app->hasProcessorClass($class)) {
                $app->registerProcessorClass($class);
            }

            $frontProcessors[] = $app->getFrontProcessorClass($class);
        }

        parent::__construct([
            'parentId'   => $parent->getId(),
            'childId'    => $child->getId(),
            'html'       => $html,
            'strModel'   => Model::getJavaScriptModelObject($child),
            'processors' => $frontProcessors,
        ]);
    }

    public static function handlerScript(): string
    {
        return <<<JAVASCRIPT

    var parent = app.getComponent(data.parentId);

    if ('string' === typeof(data.html)) {
        var childElement = document.createElement('div');
        childElement.innerHTML = data.html;
        childElement = childElement.firstChild;
        parent.childrenElement.append(childElement);
    } else {
        var childElement = parent.element.querySelector('#gphp-' + data.childId);
    }

    var model = null;
    var str = 'model = ' + data.strModel + ';';
    eval(str);
    var child = new GluePHP.Component(data.childId, app, model, childElement);
    parent.addComponent(child);

    data.processors.forEach(function(id) {
        app.processors[id](child);
    });

JAVASCRIPT;
    }
}
