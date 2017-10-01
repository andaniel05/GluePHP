<?php

namespace Andaniel05\GluePHP\Update;

interface UpdateResultInterface
{
    public function getId(): string;

    public function getUpdate(): UpdateInterface;

    public function getErrors(): array;

    public function addError(string $key, string $message): void;
}
