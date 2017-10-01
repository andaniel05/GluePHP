<?php

use Andaniel05\GluePHP\AbstractApp;

return new class('') extends AbstractApp
{
    public function html(): ?string
    {
        $di = new RecursiveDirectoryIterator(
            ROOT_DIR . '/tests/Unit/FrontEnd/GluePHP/', RecursiveDirectoryIterator::SKIP_DOTS
        );
        $it = new RecursiveIteratorIterator($di);

        $glueAppsTests = '';
        foreach ($it as $file) {
            if (substr(basename($file), -8) == '.test.js') {
                $glueAppsTests .= file_get_contents($file);
            }
        }

        $glueApps = $this->getAsset('glueapps');
        $glueAppsSource = (isset($_GET['compress']) && $_GET['compress'] == true) ?
            $glueApps->getContent() : $glueApps->getMinimizedContent();

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test App</title>
    <link href="/node_modules/mocha/mocha.css" rel="stylesheet" />
</head>
<body>
    <div id="mocha"></div>
    <script src="/node_modules/mocha/mocha.js"></script>
    <script src="/node_modules/chai/chai.js"></script>
    <script src="/node_modules/sinon-chai/lib/sinon-chai.js"></script>
    <script src="/node_modules/sinon/pkg/sinon.js"></script>

    <script>{$glueAppsSource}</script>

    <script>
    mocha.setup('tdd');
    assert = chai.assert;
    </script>

    <script>{$glueAppsTests}</script>
    <script>runner = mocha.run();</script>
</body>
</html>
HTML;
    }
};
