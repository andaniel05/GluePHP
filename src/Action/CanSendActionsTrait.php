<?php

namespace Andaniel05\GluePHP\Action;

trait CanSendActionsTrait
{
    protected $sendActions = true;

    public function canSendActions(): bool
    {
        return $this->sendActions;
    }

    public function setSendActions(bool $sendActions): void
    {
        $this->sendActions = $sendActions;
    }
}
