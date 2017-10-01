<?php

namespace PlatformPHP\GlueApps\Request;

use PlatformPHP\GlueApps\Update\UpdateInterface;

interface RequestInterface
{
    public function getAppToken(): string;

    public function getStatus(): ?string;

    public function getServerUpdates(): array;

    public function addServerUpdate(UpdateInterface $update): void;

    public function getEventName(): string;

    public function getEventData(): array;
}