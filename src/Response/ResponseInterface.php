<?php

namespace Andaniel05\GluePHP\Response;

use Andaniel05\GluePHP\AbstractApp;
use Andaniel05\GluePHP\Action\AbstractAction;
use Andaniel05\GluePHP\Update\UpdateResultInterface;
use Andaniel05\GluePHP\Update\UpdateInterface;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
interface ResponseInterface
{
    public function getApp(): AbstractApp;

    public function getCode(): int;

    public function getUpdateResults(): array;

    public function addUpdateResult(UpdateResultInterface $result): void;

    public function getActions(): array;

    public function addAction(AbstractAction $action): void;

    public function toJSON(): string;

    public function canSendActions(): bool;

    public function setSendActions(bool $sendActions): void;
}
