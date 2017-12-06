<?php

namespace Andaniel05\GluePHP\Extend\Polymer;

use Andaniel05\ComposedViews\Asset\ScriptAsset;
use Andaniel05\ComposedViews\Asset\ImportAsset;
use Andaniel05\ComposedViews\HtmlElement\HtmlElementInterface;
use Andaniel05\ComposedViews\HtmlElement\HtmlElement;
use function Andaniel05\GluePHP\jsVal;
use Andaniel05\GluePHP\Component\AbstractComponent;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class WebComponent extends AbstractComponent
{
    protected $element;
    protected $importUri;
    protected $bindEvents = [];
    protected $bindProperties = [];

    public function __construct(?string $id, string $tag, string $importUri)
    {
        $this->element = new HtmlElement($tag);
        $this->importUri = $importUri;

        parent::__construct($id);
    }

    public function assets(): array
    {
        $webComponentsLoader = new ScriptAsset(
            'webcomponents-loader',
            'webcomponentsjs/webcomponents-loader.js'
        );

        $polymer = new ImportAsset(
            'polymer',
            'polymer/polymer-element.html',
            'webcomponents-loader'
        );

        $assets = [$webComponentsLoader, $polymer];

        if (is_string($this->importUri) && ! empty($this->importUri)) {
            $import = new ImportAsset(
                'import',
                $this->importUri,
                'polymer'
            );
            $assets[] = $import;
        }

        return $assets;
    }

    public function processors(): array
    {
        return [WebComponentProcessor::class];
    }

    public function bindEvents(): array
    {
        return $this->bindEvents;
    }

    public function bindProperties(): array
    {
        return $this->bindProperties;
    }

    public function html(): ?string
    {
        $this->element->setContent([$this->renderizeChildren()]);

        return $this->element->html();
    }

    public function getElement(): HtmlElementInterface
    {
        return $this->element;
    }

    public function setElement(HtmlElementInterface $element)
    {
        $this->element = $element;
    }

    public function constructorScript(): ?string
    {
        $model = $this->getModel();
        $modelArray = $model->toArray();

        $bindPropertiesArray = [];
        foreach ($this->bindProperties() as $local => $remote) {
            $attr = $local;
            $property = $remote;

            if (! is_string($attr) && is_string($property)) {
                $attr = $property;
            }

            if (! isset($modelArray[$attr])) {
                throw new Exception\UndefinedGlueAttributeException($attr);
            }

            $bindPropertiesArray[$attr] = $property;
        }

        $bindEvents = jsVal($this->bindEvents());
        $bindProperties = json_encode($bindPropertiesArray);

        return <<<JAVASCRIPT
    component._bindEvents = {$bindEvents};
    component._bindProperties = {$bindProperties};
JAVASCRIPT;
    }

    public function getTagName(): string
    {
        return $this->element->getTag();
    }

    public function getImportUri(): string
    {
        return $this->importUri;
    }
}
