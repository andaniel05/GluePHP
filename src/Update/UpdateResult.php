<?php

namespace PlatformPHP\GlueApps\Update;

class UpdateResult implements UpdateResultInterface
{
    protected $id;
    protected $update;
    protected $errors = [];

    public function __construct(UpdateInterface $update, string $id)
    {
        $this->update = $update;
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUpdate(): UpdateInterface
    {
        return $this->update;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function addError(string $key, string $message): void
    {
        $this->errors[$key] = $message;
    }
}