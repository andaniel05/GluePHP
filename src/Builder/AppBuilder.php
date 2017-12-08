<?php

namespace Andaniel05\GluePHP\Builder;

use Andaniel05\ComposedViews\Builder\PageBuilder;
use Andaniel05\ComposedViews\Builder\Event\BuilderEvent;
use Andaniel05\GluePHP\AbstractApp;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class AppBuilder extends PageBuilder
{
    public function __construct()
    {
        parent::__construct();

        $this->onTag('app', [$this, 'onAppTag']);
        $this->onTagPopulation('app', [$this, 'onPageTagPopulation']);
    }

    public function onAppTag(BuilderEvent $event)
    {
        $this->onPageTag($event);

        $element = $event->getXMLElement();
        $app = $event->getEntity();

        if (! $app instanceof AbstractApp) {
            throw new Exception\InvalidAppClassException;
        }

        $controllerPath = (string) $element['controller'];
        $basePath = (string) $element['base-path'];

        $app->setControllerPath($controllerPath);
        $app->setBasePath($basePath);
    }

    public function buildApp(string $xml)
    {
        $app = $this->build($xml);

        if (! $app instanceof AbstractApp) {
            throw new Exception\InvalidAppException;
        }

        return $app;
    }
}
