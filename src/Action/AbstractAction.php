<?php

namespace PlatformPHP\GlueApps\Action;

abstract class AbstractAction
{
    protected $id;
    protected $data = [];
    protected $sent = false;

    public function __construct($data, ?string $id = null)
    {
        $this->data = $data;
        $this->id = $id ?? uniqid('action');
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getData()
    {
        return $this->data;
    }

    public function isSent(): bool
    {
        return $this->sent;
    }

    public function send(bool $value = true): void
    {
        $this->sent = $value;
    }

    final public static function handlerScriptWrapper(): string
    {
        $handlerScript = static::handlerScript();

        return <<<JAVASCRIPT
function(data, app) {
    {$handlerScript}
}
JAVASCRIPT;
    }

    abstract public static function handlerScript(): string;
}
