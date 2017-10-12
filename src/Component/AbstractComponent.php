<?php

namespace Andaniel05\GluePHP\Component;

use Andaniel05\GluePHP\AbstractApp;
use Andaniel05\GluePHP\Action\{AbstractAction, UpdateAttributeAction};
use Andaniel05\GluePHP\Component\Model\{ModelInterface, Model};
use Andaniel05\ComposedViews\Component\AbstractComponent as AbstractViewComponent;

abstract class AbstractComponent extends AbstractViewComponent
{
    protected $app;

    public function __construct(?string $id = null)
    {
        if ( ! $id) $id = uniqid('comp_');

        parent::__construct($id);
    }

    public function getApp(): ?AbstractApp
    {
        return $this->page;
    }

    public function setApp(?AbstractApp $app): void
    {
        $this->app = $this->page = $app;
    }

    public function baseUrl(string $assetUrl = ''): string
    {
        return $this->page ? $this->page->baseUrl($assetUrl): $assetUrl;
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

                    $updateAction = new UpdateAttributeAction(
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

        foreach ($this->getAllComponents() as $component) {
            $result .= <<<HTML
<div class="cv-component cv-{$component->getId()}" id="cv-{$component->getId()}">
    {$component->html()}
</div>
HTML;
        }

        return $result;
    }
}
