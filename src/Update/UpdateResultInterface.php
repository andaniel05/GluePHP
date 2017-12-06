<?php

namespace Andaniel05\GluePHP\Update;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
interface UpdateResultInterface
{
    public function getId(): string;

    public function getUpdate(): UpdateInterface;

    public function getErrors(): array;

    public function addError(string $key, string $message): void;
}
