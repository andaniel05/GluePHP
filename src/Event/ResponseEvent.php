<?php

namespace PlatformPHP\GlueApps\Event;

use Symfony\Component\EventDispatcher\Event;
use PlatformPHP\GlueApps\Response\ResponseInterface;

class ResponseEvent extends Event
{
    protected $response;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}