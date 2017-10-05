<?php

namespace Andaniel05\GluePHP;

use Andaniel05\GluePHP\Action\{AbstractAction, CanSendActionsTrait, EvalAction,
    RegisterAction, UpdateAttributeAction};
use Andaniel05\GluePHP\Asset\{GluePHPScript, AppScript};
use Andaniel05\GluePHP\Processor\{BindEventsProcessor, BindDataProcessor};
use Andaniel05\GluePHP\Request\RequestInterface;
use Andaniel05\GluePHP\Response\{ResponseInterface, Response};
use Andaniel05\GluePHP\Event\{Event, RequestEvent, ResponseEvent};
use Andaniel05\GluePHP\Update\{UpdateInterface,
    UpdateResultInterface, UpdateResult, Update};
use Andaniel05\GluePHP\Component\AbstractComponent;
use Andaniel05\GluePHP\Component\Model\{ModelInterface, Model};
use Symfony\Component\EventDispatcher\{EventDispatcherInterface, EventDispatcher};
use Andaniel05\ComposedViews\{AbstractPage, PageEvents};
use Andaniel05\ComposedViews\Event\AfterInsertionEvent;
use Andaniel05\ComposedViews\Component\AbstractComponent as AbstractPageComponent;

abstract class AbstractApp extends AbstractPage
{
    use CanSendActionsTrait;

    protected $id = 'app';
    protected $token;
    protected $controllerPath;
    protected $dispatcher;
    protected $statusCheck = false;
    protected $request;
    protected $response;
    protected $actionClasses = [];
    protected $processorClasses = [];
    protected $componentClasses = [];
    protected $debug = false;

    public function __construct(string $controllerPath, string $baseUrl = '', ?EventDispatcherInterface $dispatcher = null)
    {
        $this->token = uniqid('app');
        $this->controllerPath = $controllerPath;

        if ( ! $dispatcher) {
            $dispatcher = new EventDispatcher();
        }

        $gluePhpScript = new GluePHPScript('gluephp', $this);
        $appScript = new AppScript('app', $this, ['gluephp']);

        $this->addAsset($gluePhpScript);
        $this->addAsset($appScript);

        parent::__construct($baseUrl, $dispatcher);

        // $dispatcher->addListener(
        //     PageEvents::AFTER_INSERTION, [$this, 'onAfterInsertion']
        // );

        $this->registerActionClass(EvalAction::class);
        $this->registerActionClass(RegisterAction::class);
        $this->registerActionClass(UpdateAttributeAction::class);

        $this->registerProcessorClass(BindEventsProcessor::class);
        $this->registerProcessorClass(BindDataProcessor::class);
    }

    public function sidebars(): array
    {
        return ['body'];
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id)
    {
        $this->id = $id;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function handle(RequestInterface $request): ResponseInterface
    {
        $requestEvent = new RequestEvent($request);
        $this->dispatcher->dispatch(AppEvents::REQUEST, $requestEvent);
        $this->request = $request;

        $response = $this->processRequest($requestEvent->getRequest());

        $responseEvent = new ResponseEvent($response);
        $this->dispatcher->dispatch(AppEvents::RESPONSE, $responseEvent);

        $this->request = null;
        $this->response = null;

        return $responseEvent->getResponse();
    }

    protected function processRequest(RequestInterface $request): ResponseInterface
    {
        $token = $this->getToken();
        if ($token != $request->getAppToken()) {
            return new Response($this, 401);
        }

        if ($this->statusCheck && $this->getStatus() != $request->getStatus()) {
            return new Response($this, 409);
        }

        $this->response = $response = new Response($this);
        $response->setSendActions($this->sendActions);

        foreach ($request->getServerUpdates() as $update) {
            $result = $this->runServerUpdate($update);
            $response->addUpdateResult($result);
        }

        $event = new Event($this, $request->getEventName(), $request->getEventData());
        $this->dispatcher->dispatch($request->getEventName(), $event);

        return $response;
    }

    protected function runServerUpdate(UpdateInterface $update): UpdateResultInterface
    {
        $result = new UpdateResult($update, "result_{$update->getId()}");

        $component = $this->getComponent($update->getComponentId());
        if ( ! $component) {
            $result->addError('component-not-found', $update->getComponentId());
            return $result;
        }

        if (method_exists($component, 'beforeUpdate')) {
            call_user_func([$component, 'beforeUpdate'], $update);
        }

        $model = Model::get(get_class($component));

        foreach ($update->getData() as $attr => $value) {
            try {
                call_user_func([$component, $model->getSetter($attr)], $value, false);
            } catch (\Exception $e) {
                $result->addError($attr, strval($value));
            }
        }

        if (method_exists($component, 'afterUpdate')) {
            call_user_func([$component, 'afterUpdate'], $update);
        }

        return $result;
    }

    public function hasStatusCheck(): bool
    {
        return $this->statusCheck;
    }

    public function setStatusCheck(bool $statusCheck): void
    {
        $this->statusCheck = $statusCheck;
    }

    public function getSnapshot(): array
    {
        $result = [];

        foreach ($this->components() as $component) {
            $model = Model::get(get_class($component));
            $data = [];
            foreach ($model->getAttributeList() as $attribute) {
                $data[$attribute] = call_user_func([$component, $model->getGetter($attribute)]);
            }
            $result[$component->getId()] = $data;
        }

        return $result;
    }

    public function getStatus(): string
    {
        return md5(http_build_query($this->getSnapshot()));
    }

    public function getRequest(): ?RequestInterface
    {
        return $this->request;
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    public function getActionClasses(): array
    {
        return $this->actionClasses;
    }

    public function hasActionClass(string $actionClass): bool
    {
        return isset($this->actionClasses[$actionClass]);
    }

    public function registerActionClass(string $actionClass, ?string $handlerId = null): void
    {
        $handlerId = $handlerId ?? uniqid('Action');
        $this->actionClasses[$actionClass] = $handlerId;

        if ($this->inProcess()) {
            $action = new RegisterAction($actionClass, $handlerId);
            $this->act($action);
        }
    }

    public function getActionHandler(string $actionClass): ?string
    {
        return $this->actionClasses[$actionClass] ?? null;
    }

    public function getControllerPath(): ?string
    {
        return $this->controllerPath;
    }

    public function setControllerPath(?string $controllerPath)
    {
        $this->controllerPath = $controllerPath;
    }

    public function registerComponentClass(string $componentClass, ?string $frontId = null): void
    {
        $frontId = $frontId ?? uniqid('Component');
        $this->componentClasses[$componentClass] = $frontId;

        if ($this->inProcess()) {
            $model = Model::get($componentClass);
            $action = new EvalAction($model->getJavaScriptClass($this));
            $this->response->addAction($action);
        }
    }

    public function getComponentClasses(): array
    {
        return $this->componentClasses;
    }

    public function hasComponentClass(string $componentClass): bool
    {
        return isset($this->componentClasses[$componentClass]);
    }

    public function getFrontComponentClass(string $componentClass): ?string
    {
        return $this->componentClasses[$componentClass];
    }

    public function updateComponentClasses(): void
    {
        foreach ($this->components() as $component) {
            $class = get_class($component);
            if ( ! $this->hasComponentClass($class)) {
                $this->registerComponentClass($class);
            }
        }
    }

    public function on(string $eventName, callable $callback): void
    {
        $this->dispatcher->addListener($eventName, $callback);
    }

    public function getProcessorClasses(): array
    {
        return $this->processorClasses;
    }

    public function registerProcessorClass(string $processorClass, ?string $frontId = null)
    {
        $frontId = $frontId ?? uniqid('Processor');

        $this->processorClasses[$processorClass] = $frontId;
    }

    public function getFrontProcessorClass(string $processorClass): ?string
    {
        return $this->processorClasses[$processorClass] ?? null;
    }

    public function __wakeup()
    {
        // Esto es un hack
        //

        $closure = function () {
            $this->sorted = [];
        };

        $closure->call($this->dispatcher);
    }

    public function isBooted(): bool
    {
        return $this->printed;
    }

    public function setBooted(bool $value)
    {
        $this->printed = $value;
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    public function setDebug(bool $value = true)
    {
        $this->debug = $value;

        if ($value) {
            $this->getAsset('gluephp')->setMinimized(false);
            $this->getAsset('app')->setMinimized(false);
        } else {
            $this->getAsset('gluephp')->setMinimized(true);
            $this->getAsset('app')->setMinimized(true);
        }
    }

    public function act(AbstractAction $action)
    {
        if ($this->inProcess()) {
            $this->response->addAction($action);
        }
    }

    public function inProcess(): bool
    {
        return $this->request && $this->response;
    }

    public function appendComponent(string $parentId, AbstractPageComponent $component): void
    {
        parent::appendComponent($parentId, $component);
        $component->setApp($this);
    }

    public function onAfterInsertion(AfterInsertionEvent $event)
    {
        $action = new AppendAction($event->getParent(), $event->getChild());
        $this->act($action);
    }
}
