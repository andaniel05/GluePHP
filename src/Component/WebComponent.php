<?php

namespace Andaniel05\GluePHP\Component;

use Andaniel05\ComposedViews\HtmlElement\{HtmlElementInterface, HtmlElement};

class WebComponent extends AbstractComponent
{
    protected $element;

    public function __construct(string $id, string $tag)
    {
        parent::__construct($id);

        $this->element = new HtmlElement($tag);
    }

    public function html(): ?string
    {
    }

    public function getElement(): HtmlElementInterface
    {
        return $this->element;
    }

    public function setElement(HtmlElementInterface $element)
    {
        $this->element = $element;
    }
}
