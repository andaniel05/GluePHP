<?php

namespace Andaniel05\GluePHP\Tests;

use Andaniel05\GluePHP\AbstractApp;
use Andaniel05\ComposedViews\PageEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class TestApp extends AbstractApp
{
    public function __construct(string $controllerPath = '', string $basePath = '', ?EventDispatcherInterface $dispatcher = null)
    {
        $controllerPath = empty($controllerPath) ? controllerUri() : $controllerPath;
        parent::__construct($controllerPath, $basePath, $dispatcher);

        $this->setDebug();

        $this->dispatcher->addListener(PageEvents::FILTER_ASSETS, function ($event) {
            $assets = $event->getAssets();
            $vuejs = $assets['vuejs'] ?? null;
            if ($vuejs) {
                $vuejs->setUri('/bower_components/vue/dist/vue.min.js');
            }
        });
    }

    public function getTitle(): string
    {
        return StaticTestCase::$currentTest;
    }

    public function html(): ?string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="{$this->var('lang')}">
<head>
    <meta charset="{$this->var('charset')}">
    <title>{$this->getTitle()}</title>
</head>
<body>
    {$this->renderSidebar('body')}

    {$this->renderAssets('scripts')}
</body>
</html>
HTML;
    }
};
