<?php

namespace Andaniel05\GluePHP\Tests;

use Andaniel05\GluePHP\AbstractApp;

class TestApp extends AbstractApp
{
    public function __construct(string $basePath = '', ?EventDispatcherInterface $dispatcher = null)
    {
        $basePath = empty($basePath) ? controllerUrl() : $basePath;
        parent::__construct($basePath, $basePath, $dispatcher);

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
