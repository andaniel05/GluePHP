<?php

namespace Andaniel05\GluePHP\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

class SeleniumTestCase extends PHPUnitTestCase
{
    protected static $globalDriver;
    protected static $fails;
    public static $currentTest = '';

    public static function setUpBeforeClass()
    {
        static::$fails = false;
        static::$globalDriver = \RemoteWebDriver::create(
            $GLOBALS['selenium_server'], \DesiredCapabilities::chrome()
        );
    }

    public function setUp()
    {
        $this->driver = static::$globalDriver;
        $this->driver->executeScript('document.open()');

        $script = <<<JAVASCRIPT
document.jsErrors = [];
window.onerror = function(message, url, lineNumber) {
    document.jsErrors.push({
        message: message,
        url: url,
        lineNumber: lineNumber,
    });
    return true;
};
JAVASCRIPT;
        $this->driver->executeScript($script);

        static::$currentTest = $this->getName();
    }

    public function tearDown()
    {
        $jsErrors = $this->driver->executeScript('return document.jsErrors;');
        $errors = count($jsErrors);
        if ($errors > 0) {
            static::$fails = true;
            $details = print_r($jsErrors, true);
            $this->fail("There are {$errors} javascript errors.\n{$details}");
        }

        $this->driver->executeScript('document.close()');

        if ($this->hasFailed()) {
            static::$fails = true;
        }

        static::$currentTest = '';
    }

    public static function tearDownAfterClass()
    {
        if ( ! static::$fails) {
            static::$globalDriver->close();
        }
    }

    public function writeDocument(string $html)
    {
        $html = str_replace('`', '\`', $html);

        $this->driver->executeScript("document.write(`$html`); document.close();");
    }

    public function script(string $script)
    {
        return $this->driver->executeScript($script);
    }
}
