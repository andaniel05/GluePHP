<?php

namespace Andaniel05\GluePHP\Asset;

use Andaniel05\GluePHP\AbstractApp;
use Andaniel05\ComposedViews\Asset\TagScriptAsset;

class GluePHPScript extends TagScriptAsset
{
    use AppAssetTrait, SleepTrait;

    public function __construct(string $id, AbstractApp $app, array $dependencies = [], array $groups = [])
    {
        $distDir = __DIR__ . '/../FrontEnd/Dist';
        $content = file_get_contents($distDir . '/GluePHP.js');
        $minimizedContent = file_get_contents($distDir . '/GluePHP.min.js');

        parent::__construct($id, $content, $dependencies, $groups);
        $this->setMinimizedContent($minimizedContent);
        $this->app = $app;
    }
}
