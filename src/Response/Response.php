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
    protected $clientUpdates = [];
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

    public function getAppToken(): string
    {
        return $this->app->getToken();
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

    public function getClientUpdates(): array
    {
        return $this->clientUpdates;
    }

    public function addClientUpdate(UpdateInterface $update): void
    {
        $this->clientUpdates[$update->getId()] = $update;
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

            $registerAction = new RegisterAction(
                $actionClass, $this->app->getActionHandler($actionClass)
            );

            $this->addAction($registerAction);
        }

        if ($this->sendActions) {

            echo json_encode([
                'id'      => $action->getId(),
                'data'    => $action->getData(),
                'handler' => $this->app->getActionHandler(get_class($action)),
            ]) . '%GLUE_MESSAGE%';

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

        $clientUpdates = [];
        foreach ($this->clientUpdates as $id => $clientUpdate) {
            $clientUpdates[$id] = [
                'id'          => $clientUpdate->getId(),
                'componentId' => $clientUpdate->getComponentId(),
                'data'        => $clientUpdate->getData(),
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
            'appToken'      => $this->app->getToken(),
            'code'          => $this->code,
            'updateResults' => $updateResults,
            'clientUpdates' => $clientUpdates,
            'actions'       => $actions,
        ]);
    }
}
