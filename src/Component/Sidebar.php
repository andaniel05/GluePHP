<?php

namespace Andaniel05\GluePHP\Component;

use Andaniel05\ComposedViews\Component\SidebarInterface;

class Sidebar extends AbstractComponent implements SidebarInterface
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
