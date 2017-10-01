<?php

namespace Andaniel05\GluePHP\Request;

use Andaniel05\GluePHP\Update\UpdateInterface;

interface RequestInterface
{
    public function getAppToken(): string;

    public function getStatus(): ?string;

    public function getServerUpdates(): array;

    public function addServerUpdate(UpdateInterface $update): void;

    public function getEventName(): string;

    public function getEventData(): array;
}
