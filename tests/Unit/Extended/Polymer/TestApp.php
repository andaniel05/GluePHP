<?php

namespace Andaniel05\GluePHP\Tests\Unit\Extended\Polymer;

use Andaniel05\GluePHP\Tests\TestApp as GluePHPTestApp;
use Andaniel05\ComposedViews\Asset\ScriptAsset;
use Andaniel05\ComposedViews\Asset\ImportAsset;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class TestApp extends GluePHPTestApp
{
    public function __construct(string $controllerPath = '')
    {
        parent::__construct($controllerPath, '/bower_components/');
    }

    public function assets(): array
    {
        return [
            'webcomponents-loader' => new ScriptAsset(
                'webcomponents-loader',
                'webcomponentsjs/webcomponents-loader.js'
            ),
            'polymer' => new ImportAsset(
                'polymer',
                'polymer/polymer-element.html',
                'webcomponents-loader'
            ),
        ];
    }

    public function html(): ?string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{$this->getTitle()}</title>

    <base href="{$this->basePath}">

    {$this->renderAsset('webcomponents-loader')}

    {$this->renderAssets('imports')}
    {$this->renderAssets('header scripts')}
</head>
<body>
    {$this->getSidebar('body')->html()}

    {$this->renderAssets('scripts')}
</body>
</html>
HTML;
    }
};
