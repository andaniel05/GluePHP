<?php

namespace Andaniel05\GluePHP\Update;

class Update implements UpdateInterface
{
    protected $id;
    protected $componentId;
    protected $data;

    public function __construct(string $componentId, array $data, ?string $id = null)
    {
        if ( ! $id) $id = uniqid('up_');

        $this->componentId = $componentId;
        $this->data = $data;
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getComponentId(): string
    {
        return $this->componentId;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
