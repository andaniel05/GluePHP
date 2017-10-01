<?php

namespace PlatformPHP\GlueApps\Response;

use PlatformPHP\GlueApps\AbstractApp;
use PlatformPHP\GlueApps\Action\AbstractAction;
use PlatformPHP\GlueApps\Update\{UpdateResultInterface, UpdateInterface};

interface ResponseInterface
{
    public function getApp(): AbstractApp;

    public function getAppToken(): string;

    public function getCode(): int;

    public function getUpdateResults(): array;

    public function addUpdateResult(UpdateResultInterface $result): void;

    public function getClientUpdates(): array;

    public function addClientUpdate(UpdateInterface $update): void;

    public function getActions(): array;

    public function addAction(AbstractAction $action): void;

    public function toJSON(): string;

    public function canSendActions(): bool;

    public function setSendActions(bool $sendActions): void;
}