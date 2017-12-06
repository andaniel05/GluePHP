<?php

namespace Andaniel05\GluePHP\Event;

use Andaniel05\GluePHP\AbstractApp;
use Andaniel05\GluePHP\Component\AbstractComponent;
use Symfony\Component\EventDispatcher\Event as SymfonyEvent;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Event extends SymfonyEvent
{
    protected $app;
    protected $name;
    protected $data;
    protected $component;

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

    public function setComponent(?AbstractComponent $component)
    {
        $this->component = $component;
    }

    public function getComponent(): ?AbstractComponent
    {
        return $this->component;
    }

    public function __get(string $name)
    {
        switch ($name) {

            case 'app':
                return $this->app;
                break;

            case 'component':
                return $this->component;
                break;
        }
    }
}
