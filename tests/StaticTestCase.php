<?php

namespace Andaniel05\GluePHP\Tests;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class StaticTestCase extends SeleniumTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->app = new TestApp('');
        $this->body = $this->app->getSidebar('body');
    }

    public function waitForResponse()
    {
        if (! $this->driver) {
            return;
        }

        do {
            $httpRequestsLen = $this->script('return app.httpRequests.length');
        } while ($httpRequestsLen > 0);
    }
}
