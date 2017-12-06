<?php

namespace Andaniel05\GluePHP\Tests\Integration\Entities\Components;

use Andaniel05\GluePHP\Extend\Polymer\WebComponent;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class CustomElement extends WebComponent
{
    /**
     * @Glue
     */
    protected $text;

    public function __construct(?string $id = null)
    {
        parent::__construct(
            $id,
            'custom-element',
            importUri('/Entities/custom-element.html')
        );
    }

    public function bindProperties(): array
    {
        return ['text' => 'textContent'];
    }
}
