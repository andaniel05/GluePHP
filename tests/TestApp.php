<?php

namespace Andaniel05\GluePHP\Tests;

use Andaniel05\GluePHP\AbstractApp;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TestApp extends AbstractApp
{
    public function __construct(string $controllerPath = '', string $basePath = '', ?EventDispatcherInterface $dispatcher = null)
    {
        $controllerPath = empty($controllerPath) ? controllerUri() : $controllerPath;
        parent::__construct($controllerPath, $basePath, $dispatcher);

        $this->setDebug();
    }

    public function getTitle(): string
    {
        return StaticTestCase::$currentTest;
    }

    public function html(): ?string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{$this->getTitle()}</title>
</head>
<body>
    {$this->getSidebar('body')->html()}

    {$this->renderAssets('scripts')}
</body>
</html>
HTML;
    }
};
