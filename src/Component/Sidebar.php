<?php

namespace Andaniel05\GluePHP\Component;

class Sidebar extends AbstractComponent
{
    public function processors(): array
    {
        return [];
    }

    public function html(): ?string
    {
        return AbstractComponent::containerView(
            $this->id, $this->renderizeChildren()
        );
    }
}
