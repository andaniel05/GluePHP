<?php

namespace Andaniel05\GluePHP\Response;

use Andaniel05\GluePHP\AbstractApp;
use Andaniel05\GluePHP\Action\{AbstractAction, CanSendActionsTrait, RegisterAction};
use Andaniel05\GluePHP\Update\{UpdateResultInterface, UpdateInterface};

class Response implements ResponseInterface
{
    use CanSendActionsTrait;

    protected $app;
    protected $code;
    protected $updateResults = [];
    protected $actions = [];

    public function __construct(AbstractApp $app, int $code = 200)
    {
        $this->app = $app;
        $this->code = $code;
        $this->sendActions = $app->canSendActions();
    }

    public function getApp(): AbstractApp
    {
        return $this->app;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getUpdateResults(): array
    {
        return $this->updateResults;
    }

    public function addUpdateResult(UpdateResultInterface $result): void
    {
        $this->updateResults[$result->getId()] = $result;
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    public function addAction(AbstractAction $action): void
    {
        $actionClass = get_class($action);
        if ( ! $this->app->hasActionClass($actionClass)) {
            $this->app->registerActionClass($actionClass);
        }

        if ($this->sendActions) {

            echo json_encode([
                'id'      => $action->getId(),
                'data'    => $action->getData(),
                'handler' => $this->app->getActionHandler(get_class($action)),
            ]) . '%G_MSG%';

            ob_flush();
            flush();

            $action->send();

        } else {
            $this->actions[$action->getId()] = $action;
        }
    }

    public function toJSON(): string
    {
        $updateResults = [];
        foreach ($this->updateResults as $id => $result) {
            $updateResults[$id] = [
                'id'       => $id,
                'updateId' => $result->getUpdate()->getId(),
                'errors'   => $result->getErrors(),
            ];
        }

        $actions = [];
        foreach ($this->actions as $id => $action) {
            $actions[$id] = [
                'id'      => $id,
                'data'    => $action->getData(),
                'handler' => $this->app->getActionHandler(get_class($action)),
            ];
        }

        return json_encode([
            'code'          => $this->code,
            'updateResults' => $updateResults,
            'actions'       => $actions,
        ]);
    }
}
