<?php

namespace Andaniel05\GluePHP\Component;

trait EventRecordTrait
{
    protected $eventRecord = [];

    public function getEventRecord(): array
    {
        return $this->eventRecord;
    }
}