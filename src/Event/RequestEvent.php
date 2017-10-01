<?php

namespace PlatformPHP\GlueApps\Event;

use Symfony\Component\EventDispatcher\Event;
use PlatformPHP\GlueApps\Request\RequestInterface;

class RequestEvent extends Event
{
    protected $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}