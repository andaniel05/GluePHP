<?php

namespace Andaniel05\GluePHP\Component;

class Sidebar extends AbstractComponent
{
    public function html(): ?string
    {
        return <<<HTML
<div class="gphp-component gphp-{$this->id}" id="gphp-{$this->id}">
    {$this->renderizeChildren()}
</div>
HTML;
    }
}
