<?php

namespace PlatformPHP\GlueApps\Event;

use PlatformPHP\GlueApps\AbstractApp;
use Symfony\Component\EventDispatcher\Event as SymfonyEvent;

class Event extends SymfonyEvent
{
    protected $app;
    protected $name;
    protected $data;

    public function __construct(AbstractApp $app, string $name, array $data)
    {
        $this->app = $app;
        $this->name = $name;
        $this->data = $data;
    }

    public function getApp(): AbstractApp
    {
        return $this->app;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getData(): array
    {
        return $this->data;
    }
}