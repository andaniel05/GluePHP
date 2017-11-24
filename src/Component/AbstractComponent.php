<?php

namespace Andaniel05\GluePHP\Component;

use Andaniel05\GluePHP\AbstractApp;
use Andaniel05\GluePHP\Action\{AbstractAction, UpdateAction};
use Andaniel05\GluePHP\Component\Model\{ModelInterface, Model};
use Andaniel05\GluePHP\Processor\{DefaultProcessor, VueProcessor};
use Andaniel05\ComposedViews\PageInterface;
use Andaniel05\ComposedViews\Component\AbstractComponent as AbstractViewComponent;
use Symfony\Component\EventDispatcher\{EventDispatcherInterface, EventDispatcher};

abstract class AbstractComponent extends AbstractViewComponent
{
    protected $app;
    protected $dispatcher;

    public function __construct(?string $id = null)
    {
        if ( ! $id) $id = strtolower(uniqid(basename(static::class)));

        parent::__construct($id);

        $this->dispatcher = new EventDispatcher;
    }

    public function processors(): array
    {
        return [
            DefaultProcessor::class,
            VueProcessor::class,
        ];
    }

    public function getApp(): ?AbstractApp
    {
        return $this->app;
    }

    public function setApp(?AbstractApp $app): void
    {
        $this->app = $this->page = $app;
    }

    public function setPage(?PageInterface $page)
    {
        parent::setPage($page);

        $this->app = $page;
    }

    public function getDispatcher(): EventDispatcherInterface
    {
        return $this->dispatcher;
    }

    public function setDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function basePath(string $assetUrl = ''): string
    {
        return $this->page ? $this->page->basePath($assetUrl): $assetUrl;
    }

    public function constructorScript(): ?string
    {
        return null;
    }

    final public function getModel(): ModelInterface
    {
        return Model::get(static::class);
    }

    public function __call(string $functionName, array $arguments)
    {
        $operation = substr($functionName, 0, 3);
        $attribute = lcfirst(substr($functionName, 3));

        $existsAttribute = in_array(
            $attribute,
            $this->getModel()->getAttributeList()
        );

        if ($existsAttribute) {
            if ($operation === 'set') {

                $this->{$attribute} = $arguments[0];

                $sendUpdateAction = isset($arguments[1]) ?
                    (bool) $arguments[1] : true;

                if ($sendUpdateAction && $this->app) {

                    $updateAction = new UpdateAction(
                        $this->getId(), $attribute, $arguments[0]
                    );

                    if ($response = $this->app->getResponse()) {
                        $response->addAction($updateAction);
                    }
                }

                return $this;

            } elseif ($operation === 'get') {
                return $this->{$attribute};
            }
        }

        throw new Exception\InvalidCallException();
    }

    public static function extendClassScript(): ?string
    {
        return null;
    }

    public function html(): ?string
    {
        return null;
    }

    public function on(string $eventName, callable $callback): void
    {
        $this->dispatcher->addListener($eventName, $callback);

        if ($this->app) {
            $this->app->on("{$this->id}.{$eventName}", $callback);
        }
    }

    public function act(AbstractAction $action)
    {
        if ($this->app) {
            $this->app->act($action);
        }
    }

    public function renderizeChildren(): ?string
    {
        $result = '';
        foreach ($this->getChildren() as $component) {

            $id = $component->getId();
            $html = $component->html();

            if (is_string($id) && is_string($html)) {
                $result .= static::containerView(
                    $component->getId(), $component->html()
                );
            }
        }

        return static::childrenContainerView($this->id, $result);
    }

    public static function containerView(string $id, string $html): string
    {
        return <<<HTML
<div class="gphp-component gphp-{$id}" id="gphp-{$id}">
    {$html}
</div>
HTML;
    }

    public static function childrenContainerView(string $id, string $html): string
    {
        return <<<HTML
<div class="gphp-children gphp-{$id}-children">
    {$html}
</div>
HTML;
    }
}
