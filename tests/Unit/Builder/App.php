<?php

namespace Andaniel05\GluePHP\Tests\Unit\Builder;

use Andaniel05\GluePHP\AbstractApp;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class App extends AbstractApp
{
    public function sidebars(): array
    {
        return ['sidebar1', 'sidebar2'];
    }

    public function html(): ?string
    {
    }
}
