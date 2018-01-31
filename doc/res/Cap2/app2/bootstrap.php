<?php

require_once 'vendor/autoload.php';

use Andaniel05\GluePHP\AbstractApp;
use Andaniel05\GluePHP\Component\AbstractComponent;

class App extends AbstractApp
{
    public function html(): ?string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>My glue app</title>
</head>
<body>
    {$this->renderSidebar('body')}

    {$this->renderAssets('scripts')}
</body>
</html>
HTML;
    }
}

class Input extends AbstractComponent
{
    /**
     * @Glue
     */
    protected $text;

    public function html(): ?string
    {
        return '<input type="text" gphp-bind-value="text">';
    }
}

class Label extends AbstractComponent
{
    /**
     * @Glue
     */
    protected $text;

    public function html(): ?string
    {
        return '<label gphp-bind-html="text"></label>';
    }
}

class Button extends AbstractComponent
{
    /**
     * @Glue
     */
    protected $text = 'Click Me!';

    public function html(): ?string
    {
        return '<button gphp-bind-html="text" gphp-bind-events="click"></button>';
    }
}
