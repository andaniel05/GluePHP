<?php

namespace Andaniel05\GluePHP;

use Andaniel05\GluePHP\Action\AbstractAction;
use Andaniel05\GluePHP\Action\CanSendActionsTrait;
use Andaniel05\GluePHP\Action\EvalAction;
use Andaniel05\GluePHP\Action\AppendAction;
use Andaniel05\GluePHP\Action\RegisterAction;
use Andaniel05\GluePHP\Action\UpdateAction;
use Andaniel05\GluePHP\Action\DeleteAction;
use Andaniel05\GluePHP\Asset\AppScript;
use Andaniel05\GluePHP\Processor\BindEventsProcessor;
use Andaniel05\GluePHP\Processor\BindValueProcessor;
use Andaniel05\GluePHP\Request\RequestInterface;
use Andaniel05\GluePHP\Response\ResponseInterface;
use Andaniel05\GluePHP\Response\Response;
use Andaniel05\GluePHP\Event\Event;
use Andaniel05\GluePHP\Event\RequestEvent;
use Andaniel05\GluePHP\Event\ResponseEvent;
use Andaniel05\GluePHP\Update\UpdateInterface;
use Andaniel05\GluePHP\Update\UpdateResultInterface;
use Andaniel05\GluePHP\Update\UpdateResult;
use Andaniel05\GluePHP\Update\Update;
use Andaniel05\GluePHP\Component\AbstractComponent;
use Andaniel05\GluePHP\Component\Sidebar;
use Andaniel05\GluePHP\Component\Model\ModelInterface;
use Andaniel05\GluePHP\Component\Model\Model;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Andaniel05\ComposedViews\Asset\ContentScriptAsset;
use Andaniel05\ComposedViews\AbstractPage;
use Andaniel05\ComposedViews\PageEvents;
use Andaniel05\ComposedViews\Event\AfterInsertionEvent;
use Andaniel05\ComposedViews\Event\AfterDeletionEvent;
use Andaniel05\ComposedViews\Component\ComponentInterface as PageComponentInterface;

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
    protected $distDir;

    public function __construct(string $controllerPath, string $basePath = '', ?EventDispatcherInterface $dispatcher = null)
    {
        $this->token = uniqid('app');
        $this->controllerPath = $controllerPath;

        if (! $dispatcher) {
            $dispatcher = new EventDispatcher();
        }

        $this->distDir = __DIR__ . '/FrontEnd/Dist';
        $gluePhpScript = new ContentScriptAsset(
            'gluephp',
            file_get_contents($this->distDir . '/GluePHP.min.js')
        );
        $appScript = new AppScript('app', $this, ['gluephp']);

        $this->addAsset($gluePhpScript);
        $this->addAsset($appScript);

        parent::__construct($basePath, $dispatcher);

        $dispatcher->addListener(
            PageEvents::AFTER_INSERTION,
            [$this, 'onAfterInsertion']
        );

        $dispatcher->addListener(
            PageEvents::AFTER_DELETION,
            [$this, 'onAfterDeletion']
        );

        $this->registerActionClass(AppendAction::class);
        $this->registerActionClass(DeleteAction::class);
        $this->registerActionClass(EvalAction::class);
        $this->registerActionClass(RegisterAction::class);
        $this->registerActionClass(UpdateAction::class);
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

        $eventName = $request->getEventName();
        $event = new Event($this, $eventName, $request->getEventData());

        $eventNameParts = explode('.', $eventName);
        if (count($eventNameParts) >= 2) {
            $componentId = $eventNameParts[0];
            $event->setComponent($this->getComponent($componentId));
        }

        $this->dispatcher->dispatch($eventName, $event);

        return $response;
    }

    protected function runServerUpdate(UpdateInterface $update): UpdateResultInterface
    {
        $result = new UpdateResult($update, "result_{$update->getId()}");

        $component = $this->getComponent($update->getComponentId());
        if (! $component) {
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
        $handlerId = $handlerId ?? uniqid(basename($actionClass));
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
        $frontId = $frontId ?? uniqid(basename($componentClass));
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

    public function hasProcessorClass(string $processorClass): bool
    {
        return isset($this->processorClasses[$processorClass]);
    }

    public function getFrontComponentClass(string $componentClass): ?string
    {
        return $this->componentClasses[$componentClass] ?? null;
    }

    public function updateComponentClasses(): void
    {
        foreach ($this->components() as $component) {
            $class = get_class($component);
            if (! $this->hasComponentClass($class)) {
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
        $frontId = $frontId ?? uniqid(basename($processorClass));
        $this->processorClasses[$processorClass] = $frontId;

        if ($this->inProcess()) {
            $scriptWrapper = $processorClass::scriptWrapper();

            $evalScript = "{$this->id}.processors.{$frontId} = {$scriptWrapper};";
            $action = new EvalAction($evalScript);
            $this->response->addAction($action);
        }
    }

    public function getFrontProcessorClass(string $processorClass): ?string
    {
        return $this->processorClasses[$processorClass] ?? null;
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
            $this->getAsset('gluephp')->setContent([file_get_contents($this->distDir . '/GluePHP.js')]);
        } else {
            $this->getAsset('gluephp')->setContent([file_get_contents($this->distDir . '/GluePHP.min.js')]);
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

    public function appendComponent(string $parentId, PageComponentInterface $component): void
    {
        parent::appendComponent($parentId, $component);
        $component->setApp($this);
    }

    public function onAfterInsertion(AfterInsertionEvent $event)
    {
        $append = function ($parent, $child, $render = true) {
            $childListeners = $child->getDispatcher()->getListeners();

            if (is_iterable($childListeners)) {
                foreach ($childListeners as $eventName => $sorted) {
                    foreach ($sorted as $listener) {
                        $this->dispatcher->addListener(
                            "{$child->getId()}.{$eventName}",
                            $listener
                        );
                    }
                }
            }

            if ($this->inProcess()) {
                $action = new AppendAction($this, $parent, $child, $render);
                $this->act($action);
            }
        };

        $parent = $event->getParent();
        $child = $event->getChild();

        $append($parent, $child);
        foreach ($child->traverse() as $nested) {
            $append($nested->getParent(), $nested, false);
        }
    }

    public function onAfterDeletion(AfterDeletionEvent $event)
    {
        if ($this->inProcess()) {
            $this->act(new DeleteAction(
                $this,
                $event->getParent(),
                $event->getChild()
            ));
        }
    }

    protected function initializeSidebars(): void
    {
        foreach ($this->sidebars() as $key => $value) {
            $sidebar = null;

            if (is_integer($key) && is_string($value)) {
                $sidebar = new Sidebar($value);
            } elseif (is_string($key) && is_array($value)) {
                $sidebar = new Sidebar($key);
                foreach ($value as $component) {
                    if ($component instanceof ComponentInterface) {
                        $sidebar->addChild($component);
                    }
                }
            }

            if ($sidebar) {
                $sidebar->setPage($this);
                $this->components[$sidebar->getId()] = $sidebar;
            }
        }
    }

    public function updateProcessorClasses(): void
    {
        foreach ($this->components() as $component) {
            foreach ($component->processors() as $processorClass) {
                $this->registerProcessorClass($processorClass);
            }
        }
    }

    public function getAllAssets(): array
    {
        $this->updateProcessorClasses();

        $assets = parent::getAllAssets();

        foreach ($this->processorClasses as $class => $frontId) {
            $assets = array_merge($assets, $class::assets());
        }

        return $assets;
    }
}
