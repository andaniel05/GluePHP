<?php

namespace Andaniel05\GluePHP\Event;

use Symfony\Component\EventDispatcher\Event;
use Andaniel05\GluePHP\Request\RequestInterface;

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
