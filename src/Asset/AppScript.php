<?php

namespace Andaniel05\GluePHP\Asset;

use function Andaniel05\GluePHP\jsVal;
use Andaniel05\GluePHP\Component\Model\Model;
use Andaniel05\GluePHP\Component\Model\Exception\InvalidTypeException;
use Andaniel05\GluePHP\AbstractApp;
use Andaniel05\ComposedViews\Asset\ContentScriptAsset;
use MatthiasMullie\Minify;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class AppScript extends ContentScriptAsset
{
    protected $app;

    public function __construct(string $id, AbstractApp $app, array $dependencies = [], array $groups = [])
    {
        $this->app = $app;

        $dependencies = implode(' ', $dependencies);
        $groups = implode(' ', $groups);

        parent::__construct($id, '', $dependencies, $groups);
    }

    public function getApp(): ?AbstractApp
    {
        return $this->app;
    }

    public function setApp(?AbstractApp $app)
    {
        $this->app = $app;
    }

    public function __sleep()
    {
        return ['id', 'dependencies', 'groups', 'used'];
    }

    public function html(): ?string
    {
        $this->content = [$this->getSource()];
        return parent::html();
    }

    public function getSource(): ?string
    {
        $this->app->updateComponentClasses();
        $this->app->updateProcessorClasses();

        $appId = $this->app->getId();

        ////////////////////
        // Action Classes //
        ////////////////////

        $registerActionClasses = '';
        foreach ($this->app->getActionClasses() as $actionClass => $handlerId) {
            $registerActionClasses .= <<<JAVASCRIPT
// {$handlerId}
(function(app) {
'use strict';
    app.actionHandlers['{$handlerId}'] = {$actionClass::handlerScriptWrapper()};
})({$appId});

JAVASCRIPT;
        }

        ////////////////
        // Processors //
        ////////////////

        $registerProcessors = '';
        foreach ($this->app->getProcessorClasses() as $processorClass => $frontId) {
            $registerProcessors .= <<<JAVASCRIPT
// {$frontId}
(function(app) {
'use strict';
    app.processors['{$frontId}'] = {$processorClass::scriptWrapper()};
})({$appId});

JAVASCRIPT;
        }

        ///////////////////////
        // Component Classes //
        ///////////////////////

        $registerComponentClasses = '';
        foreach ($this->app->getComponentClasses() as $class => $frontClassId) {
            $model = Model::get($class);
            $registerComponentClasses .= $model->getJavaScriptClass($this->app);
        }

        ///////////////////////
        // Create Components //
        ///////////////////////

        $createComponents = '';
        foreach ($this->app->components() as $component) {
            $componentClass = $this->app->getFrontComponentClass(get_class($component));
            $model = $component->getModel();
            $jsModel = Model::getJavaScriptModelObject($component);

            $applyProcessors = '';
            foreach ($component->processors() as $processorClass) {
                $processorFrontId = $this->app->getFrontProcessorClass($processorClass);
                $applyProcessors .= "app.processors['{$processorFrontId}'](component);\n";
            }

            $createComponents .= <<<JAVASCRIPT
(function (app) {
'use strict';

    var model = {$jsModel};
    var element = document.querySelector('#gphp-{$component->getId()}');
    var CClass = app.componentClasses['{$componentClass}'];
    var component = new CClass('{$component->getId()}', app, model, element);

    (function() {
        {$component->constructorScript()}
    }).call(component);

    app.addComponent(component);

    {$applyProcessors}

})({$appId});

JAVASCRIPT;
        }

        $registerInitialEvents = '';
        foreach ($this->app->getEventRecord() as $eventName => $data) {
            $dataStr = jsVal($data);
            $registerInitialEvents .= "window.{$appId}.registerEvent('{$eventName}', {$dataStr});\n";
        }

        $setDebug = '';
        if ($this->app->isDebug()) {
            $setDebug = "window.{$appId}.debug = true;";
        }

        $source = <<<JAVASCRIPT

function documentReady() {

    window.{$appId} = new GluePHP.App(
        '{$this->app->getControllerPath()}',
        '{$this->app->getToken()}'
    );
    {$setDebug}

    // Define los manejadores de las acciones.
    //

    {$registerActionClasses}

    // Define los procesadores.
    //

    {$registerProcessors}

    // Define las clases de los componentes.
    //

    {$registerComponentClasses}

    // Crea, inicializa y procesa los componentes.
    //

    {$createComponents}

    // Registra los eventos iniciales
    //

    {$registerInitialEvents}
};

if (document.readyState != 'loading') {
    documentReady();
}
else {
    document.addEventListener('DOMContentLoaded', documentReady)
}

JAVASCRIPT;

        if (! $this->app->isDebug()) {
            $source = (new Minify\JS($source))->minify();
        }

        return $source;
    }
}
