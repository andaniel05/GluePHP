<?php

namespace Andaniel05\GluePHP\Tests\Functional\FrontEnd;

use PHPUnit\Framework\TestCase;
use Andaniel05\GluePHP\Tests\SeleniumTestCase;

/**
 * Con este test se comprueba desde phpunit que todos los test unitarios JavaScript
 * del módulo GluePHP se ejecuten sin errores. Tanto en el script original como en
 * el comprimido.
 *
 * Si el test pasa la ventana del navegador se cerrará automáticamente mientras que
 * en caso contrario se mantendrá abierta donde se podrá ver el informe sobre los
 * tests que han fallado.
 *
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class GluePHPScriptTest extends TestCase
{
    public function setUp()
    {
        $this->driver = SeleniumTestCase::getDriver();
    }

    public function testAllGluePHPScriptUnitTestsAreExecutedWithoutErrors()
    {
        $app = __DIR__ . '/TestApp.php';
        $this->driver->get(appUri($app, [], false));

        $this->verify();
    }

    public function testAllGluePHPScriptUnitTestsOfMinimizedVersionAreExecutedWithoutErrors()
    {
        $app = __DIR__ . '/TestApp.php';
        $this->driver->get(appUri($app, ['compress' => true], false));

        $this->verify();
    }

    public function verify()
    {
        $this->total = $this->driver->executeScript('return runner.total');
        $this->failures = $this->driver->executeScript('return runner.failures');

        $this->assertGreaterThan(0, $this->total);
        $this->assertEquals(0, $this->failures);
    }
}
