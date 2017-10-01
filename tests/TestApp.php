<?php

namespace PlatformPHP\GlueApps\Tests;

use PlatformPHP\GlueApps\AbstractApp;

class TestApp extends AbstractApp
{
    public function __construct(string $baseUrl = '', ?EventDispatcherInterface $dispatcher = null)
    {
        $baseUrl = empty($baseUrl) ? controllerUrl() : $baseUrl;
        parent::__construct($baseUrl, $baseUrl, $dispatcher);

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
