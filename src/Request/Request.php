<?php

namespace Andaniel05\GluePHP\Request;

use Andaniel05\GluePHP\Update\UpdateInterface;
use Andaniel05\GluePHP\Update\Update;

class Request implements RequestInterface
{
    protected $appToken;
    protected $status;
    protected $eventName;
    protected $eventData = [];
    protected $serverUpdates = [];

    public function __construct(string $appToken, ?string $status, string $eventName, array $eventData = [])
    {
        $this->appToken  = $appToken;
        $this->status    = $status;
        $this->eventName = $eventName;
        $this->eventData = $eventData;
    }

    public function getAppToken(): string
    {
        return $this->appToken;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getServerUpdates(): array
    {
        return $this->serverUpdates;
    }

    public function addServerUpdate(UpdateInterface $update): void
    {
        $this->serverUpdates[$update->getId()] = $update;
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }

    public function getEventData(): array
    {
        return $this->eventData;
    }

    public static function createFromJSON(string $json): ?Request
    {
        $data = json_decode($json, true);

        if (! is_array($data)) {
            return null;
        }

        $request = new Request(
            $data['appToken'],
            $data['status'],
            $data['eventName'],
            $data['eventData']
        );

        if (is_array($data['serverUpdates'])) {
            foreach ($data['serverUpdates'] as $def) {
                $id = $def['id'] ?? uniqid('su_');
                $update = new Update($def['componentId'], $def['data'], $id);
                $request->addServerUpdate($update);
            }
        }

        return $request;
    }
}
