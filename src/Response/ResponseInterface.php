<?php

namespace Andaniel05\GluePHP\Response;

use Andaniel05\GluePHP\AbstractApp;
use Andaniel05\GluePHP\Action\AbstractAction;
use Andaniel05\GluePHP\Update\{UpdateResultInterface, UpdateInterface};

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
