<?php

namespace PlatformPHP\GlueApps\Asset;

use PlatformPHP\GlueApps\AbstractApp;
use PlatformPHP\ComposedViews\Asset\TagScriptAsset;

class GlueAppsScript extends TagScriptAsset
{
    use AppAssetTrait, SleepTrait;

    public function __construct(string $id, AbstractApp $app, array $dependencies = [], array $groups = [])
    {
        $distDir = __DIR__ . '/../FrontEnd/Dist';
        $content = file_get_contents($distDir . '/GlueApps.js');
        $minimizedContent = file_get_contents($distDir . '/GlueApps.min.js');

        parent::__construct($id, $content, $dependencies, $groups);
        $this->setMinimizedContent($minimizedContent);
        $this->app = $app;
    }
}
