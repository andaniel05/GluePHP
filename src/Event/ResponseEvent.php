<?php

namespace Andaniel05\GluePHP\Event;

use Symfony\Component\EventDispatcher\Event;
use Andaniel05\GluePHP\Response\ResponseInterface;

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
