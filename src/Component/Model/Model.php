<?php

namespace Andaniel05\GluePHP\Component\Model;

use Andaniel05\GluePHP\AbstractApp;
use function Andaniel05\GluePHP\jsVal;
use Andaniel05\GluePHP\Component\AbstractComponent;

class Model implements ModelInterface
{
    protected $class;
    protected $data = [];
    private static $cache = [];

    public function __construct(string $class)
    {
        if (! class_exists($class)) {
            throw new Exception\ClassNotFoundException($class);
        }

        $reflection = new \ReflectionClass($class);

        if (! $reflection->isSubclassOf(AbstractComponent::class) &&
            $class != AbstractComponent::class) {
            throw new Exception\InvalidComponentClassException($class);
        }

        foreach ($reflection->getProperties() as $property) {
            $doc = $property->getDocComment();
            $annotation = null;
            foreach (Annotation::parseString($doc) as $a) {
                if ('Glue' == $a->getName()) {
                    $annotation = $a;
                    break;
                }
            }

            if (! $annotation) {
                continue;
            }

            $attr   = $property->getName();
            $getter = $annotation->getAttribute('getter') ?? 'get'.ucfirst($attr);
            $setter = $annotation->getAttribute('setter') ?? 'set'.ucfirst($attr);

            $this->data[$attr] = [
                'getter' => $getter,
                'setter' => $setter,
            ];
        }

        $this->class = $class;
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getAttributeList(): array
    {
        return array_keys($this->data);
    }

    public function getGetter(string $attribute): ?string
    {
        return $this->data[$attribute]['getter'] ?? null;
    }

    public function getSetter(string $attribute): ?string
    {
        return $this->data[$attribute]['setter'] ?? null;
    }

    public function getExtendClassScript(): ?string
    {
        return $this->class::extendClassScript();
    }

    public function getJavaScriptClass(AbstractApp $app): ?string
    {
        $appId = $app->getId();
        $frontClassId = $app->getFrontComponentClass($this->class);

        $jsModelSetters = '';
        foreach ($this->toArray() as $attr => $def) {
            $setter = $this->getSetter($attr);

            $jsModelSetters .= <<<JAVASCRIPT
CClass.prototype.{$setter} = function(val, registerUpdate = true) {

    this.model['{$attr}'] = val;

    if (true === registerUpdate) {
        this.app.registerUpdate(this.id, '{$attr}', val);
    }
};
JAVASCRIPT;
        }

        $extendClassScript = $this->getExtendClassScript();

        return <<<JAVASCRIPT
// {$frontClassId}
(function(app) {
'use strict';

    var CClass = function(id, app, model, html) {
        GluePHP.Component.call(this, id, app, model, html);
    };

    CClass.prototype = Object.create(GluePHP.Component.prototype);
    CClass.prototype.constructor = CClass;

{$jsModelSetters}

{$extendClassScript}

    app.componentClasses['{$frontClassId}'] = CClass;

})({$appId});

JAVASCRIPT;
    }

    public static function get(string $class): ?ModelInterface
    {
        if (! isset(static::$cache[$class])) {
            static::$cache[$class] = new Model($class);
        }

        return static::$cache[$class];
    }

    public static function set(string $class, ModelInterface $model)
    {
        static::$cache[$class] = $model;
    }

    public static function getJavaScriptModelObject(AbstractComponent $component): string
    {
        $model = static::get(get_class($component));

        $jsModel = '{';
        foreach ($model->toArray() as $attr => $def) {
            $value = call_user_func([$component, $model->getGetter($attr)]);
            $strVal = jsVal($value);
            $jsModel .= "{$attr}: {$strVal},";
        }
        $jsModel .= '}';

        return $jsModel;
    }
}
