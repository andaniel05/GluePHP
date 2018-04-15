# Capítulo 9. Profundizando en la creación de componentes. #

En el capítulo 1 se mostró la forma de crear tipos de componentes para GluePHP. Recordemos que la tarea consiste de los siguientes pasos:

1. Crear una clase descendiente de `Andaniel05\GluePHP\Component\AbstractComponent`.
2. Declarar los *glue attributes* especificando la anotación `@Glue` sobre los respectivos atributos de la clase.
3. Declarar funcionalidades especiales(*binding* y emisión de eventos) de ciertos elementos de la vista.

En este capítulo se mostrará de manera profunda, ciertas características adicionales que presentan los componentes y que se deben de tener en cuenta a la hora crear sus tipos.

## 1. Personalizando los *getters* y los *setters* de los *glue attributes*. ##

Tal y como se explicó en el capítulo 1, por cada *glue attribute* existente en la clase, existirán dos métodos para las operaciones *getters* y *setters*, donde sus nombres cumplirán con el formato *camelCase* y se compondrán de las palabras *get* o *set* según sea el caso, seguido del nombre del *glue attribute*. Por defecto estos métodos poseen cierta lógica interna pero pueden ser redefinidos si se desea añadirles alguna lógica personalizada.

En el siguiente ejemplo se han definidos ambos métodos con el objetivo de soportar solo el tipo de datos *string*. En el caso del método *setter* es de destacar la presencia del segundo argumento `bool $sendAction = true` y de la sentencia `$this->_set('text', $text, $sendAction);`. La existencia de ambos tiene un carácter obligatorio en todo método *setter* redefinido ya que forman parte del uso interno que GluePHP les da a los componentes.

```php
class MyComponent extends AbstractComponent
{
    /**
     * @Glue
     */
    protected $text;

    public function setText(string $text, bool $sendAction = true)
    {
        $this->_set('text', $text, $sendAction);

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }
}
```

## 2. Eventos antes y después de las actualizaciones en el servidor. ##

Cuando en una etapa de procesamiento se produce una actualización de un componente en el servidor, lo que se hace es invocar a los respectivos métodos *setters* con los nuevos valores. Estas invocaciones se hacen siguiendo el orden en el que se produjeron los cambios de los respectivos *glue attributes* en el navegador. Dado que por lo general, este orden de ocurrencia no sigue un patrón determinado, existirá una limitación si se necesita redefinir un método *setter* donde su lógica necesite ser ejecutada siempre después de la actualización de otro *glue attribute*.

Para solucionar esta limitación existen los eventos 'beforeUpdate' y 'afterUpdate' de los componentes. Como lo describen sus nombres, el primero se produce antes de realizar la actualización, mientras que el segundo después de efectuar la misma. Para ello, se deben definir métodos con igual nombre en la clase del componente donde se puede especificar la lógica deseada. Como puede ver en el siguiente fragmento, ambos métodos recibirán un objeto del tipo `Andaniel05\GluePHP\Update\UpdateInterface` a través del cuál se podrá conocer toda la información de la actualización.

```php

use Andaniel05\GluePHP\Update\UpdateInterface;

class MyComponent extends AbstractComponent
{
    // ...

    public function beforeUpdate(UpdateInterface $update)
    {
    }

    public function afterUpdate(UpdateInterface $update)
    {
    }
}
```

## 3. Mostrando los componentes hijos. ##

Generalmente cuando se tiene un componente padre se necesita que la vista del mismo incluya también todas las vistas de sus hijos. Para hacer sencilla esta tarea, existe en la clase base de los componentes una función llamada `renderizeChildren()` que devuelve todas las vistas de sus hijos.

El siguiente ejemplo muestra su uso:

```php
class OrderedList extends AbstractComponent
{
    public function html(): ?string
    {
        return "<ol>{$this->renderizeChildren()}</ol>";
    }
}

class Item extends AbstractComponent
{
    protected $text;

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text)
    {
        $this->text = $text;
    }

    public function html(): ?string
    {
        return "<li>{$this->text}</li>";
    }
}

$item1 = new Item('li1');
$item1->setText('Item 1');

$item2 = new Item('li2');
$item2->setText('Item 2');

$list = new OrderedList('ol');
$list->addChild($item1);
$list->addChild($item2);

echo $list->html();
```

El resultado del *script* anterior imprimirá la siguiente estructura HTML:

```html
<ol>
    <div class="gphp-children gphp-ol-children">
        <div class="gphp-component gphp-li1" id="gphp-li1">
            <li>Item 1</li>
        </div>
        <div class="gphp-component gphp-li2" id="gphp-li2">
            <li>Item 2</li>
        </div>
    </div>
</ol>
```

Como puede ver el resultado incluye ciertos elementos que no pertenecen propiamente ni a la vista del padre ni a la de sus hijos. Estos elementos son añadidos por GluePHP para identificar en el DOM donde se almacenan los componentes hijos.

## 4. Uso de rutas relativas en el componente. ##

Gracias a la función `basePath(string $assetUrl = ''): string` presente en la clase base de los componentes, es posible combinar muy fácilmente la ruta pasada a su argumento con la ruta base de la aplicación del componente en caso de que exista.

En el siguiente ejemplo se ha creado una aplicación donde se le ha especificado que su ruta base es `http://localhost/`. Además se ha creado e insertado en la misma un componente con el que se muestra como al llamar a la función `basePath()` sobre el mismo, se devuelve un resultado que combina la ruta base de la aplicación con la ruta pasada como argumento.

```php
$app = new App('http://localhost/controller.php', 'http://localhost/');
$component = new MyComponent;

$app->appendComponent('body', $component);

echo $component->basePath('img/logo.png'); // http://localhost/img/logo.png
```

## 5. Extendiendo las clases JavaScript de los componentes. ##

Tal y como se ha comentado varias veces a lo largo del libro, cada *glue component* se forma de dos instancias completamente sincronizadas, una en el servidor y otra en el navegador. Hasta este momento, solo hemos mostrado el trabajo desde el *backend*, y es que GluePHP se enfoca en el desarrollo con PHP, lo que significa que la mayoría de sus funcionalidades están pensadas para este. No obstante, dada la naturaleza dual de este tipo de aplicaciones, es necesario tener en cuenta también ciertas necesidades que pueden existir relacionadas con el *frontend*.

Una de esas necesidades suele ser el tener que extender la clase JavaScript de algún tipo de componente. Esto se puede hacer muy fácilmente desde la propia clase PHP implementando un método estático de nombre `extendClassScript(): ?string`.

En el siguiente ejemplo se ha añadido el método `showAlert(text)` a la clase JavaScript del componente MyComponent. Es importante destacar que en el contexto de ese código JavaScript, CClass se refiere a la clase del componente.

```php
class MyComponent extends AbstractComponent
{
    // ...

    public static function extendClassScript(): ?string
    {
        return <<<JAVASCRIPT
CClass.prototype.showAlert = function(text) {
    alert(text);
};
JAVASCRIPT;
    }
}
```

De esta manera, cuando en la aplicación exista un componente de este tipo, si sobre la instancia del navegador se invoca la llamada a la función `component.showAlert('Hola');` se mostrará una alerta nativa con la palabra 'Hola'.

>Tenga en cuenta que hasta este momento no hemos explicado como trabajar con el *frontend* por lo que usted aún no conoce como obtener un componente en el navegador. Esto y mucho más lo conocerá más adelante cuando abordemos el capítulo dedicado completamente a trabajar con el *frontend*.

## 6. Inyectando código en la construcción de instancias JavaScript. ##

Otra de las necesidades que se puede tener relacionada con la parte *frontend* de los componentes es la de personalizar de forma individual la construcción de las instancias. Para ello se debe crear en la clase PHP del componente un método llamado `constructorScript(): ?string` el cual debe devolver el código JavaScript deseado.

El siguiente ejemplo muestra como implementar esta función. En el mismo se ha definido un tipo de componente llamado MyComponent y se han creado dos instancias de este tipo.

```php

use function Andaniel05\GluePHP\jsVal;

class MyComponent extends AbstractComponent
{
    public $data;

    public function constructorScript(): ?string
    {
        $value = jsVal($this->data);

        if (is_string($this->data)) {
            return "this.text = {$value};";
        } elseif (is_numeric($this->data)) {
            return "this.number = {$value};";
        }
    }
}

$component1 = new MyComponent;
$component1->data = 'Hello World';

$component2 = new MyComponent;
$component2->data = 12345;
```

Si ambos componentes se insertan en una aplicación, en el navegador la instancia del componente 1 tomará la propiedad 'text' con el texto 'Hello World', mientras que la instancia del componente 2 tomará una propiedad llamada 'number' y su valor será 12345.

Es muy importante destacar el uso de la función `jsVal()`. La misma es un *helper* para la conversión de valores desde PHP a JavaScript. Esta función recibe un argumento que puede ser de los tipos *string*, *integer*, *double*, *boolean*, *NULL*, *array*, *object*, y **devuelve una cadena con el valor preparado para ser combinado con código JavaScript**. El siguiente ejemplo muestra los diferentes casos:

```php
jsVal('Hi Andy'); // 'Hi Andy'
jsVal(29); // 29
jsVal(12.345); // 12.345
jsVal(true); // true
jsVal(null); // null
jsVal(['name' => 'Andy']); // {name: 'Andy'}
jsVal([1, 2, 3, 4, 5]); // [1, 2, 3, 4, 5]
```

## 7. Introducción a los procesadores. ##

Cuando en el capítulo 1 nos desempeñamos con el rol de desarrollador de componentes, especificamos algunos atributos especiales sobre los elementos de las vistas para indicar ciertas funcionalidades como el *Data Binding* o la fuente de los eventos. Toda esa magia y mucho más se logra gracias a los procesadores, los cuales constituyen una de las piedras angulares más importantes en el funcionamiento de GluePHP.

Cuando en el *frontend* se instancia un componente, inmediatamente se aplican sobre el mismo varios tipos de procesadores. Un procesador no es más que una determinada lógica JavaScript que se aplica sobre un componente *frontend* recién creado.

No todos los componentes se procesan de la misma manera, y es en su clase PHP donde se especifica cuales son los procesadores que se aplican para un determinado tipo. Esta especificación se hace a través del método `processors(): array` el cual devuelve un *array* con los nombres de los  mismos. En la clase `Andaniel05\GluePHP\Component\AbstractComponent` existen predefinidos una serie de procesadores, y es gracias a esto, que hasta el momento no hemos tenido que especificar ningún procesador para los tipos de componentes que hemos creado. Seguidamente se muestra un fragmento de la clase `Andaniel05\GluePHP\Component\AbstractComponent` para mostrarle todos los procesadores registrados por defecto.

```php

namespace Andaniel05\GluePHP\Component;

// ...
use Andaniel05\GluePHP\Processor\BindValueProcessor;
use Andaniel05\GluePHP\Processor\BindEventsProcessor;
use Andaniel05\GluePHP\Processor\BindAttributesProcessor;
use Andaniel05\GluePHP\Processor\BindHtmlProcessor;
use Andaniel05\GluePHP\Processor\ShortEventsProcessor;
// ...

abstract class AbstractComponent extends AbstractViewComponent
{
    // ...

    public function processors(): array
    {
        return [
            BindValueProcessor::class,
            BindEventsProcessor::class,
            BindAttributesProcessor::class,
            BindHtmlProcessor::class,
            ShortEventsProcessor::class,
        ];
    }

    // ...
}
```

Como puede ver, también los procesadores se definen con clases PHP. En el próximo capítulo se abordará el tema acerca de su creación, pero de momento, explicaremos con profundidad todos los que vienen incluidos por defecto en GluePHP, donde muchos de ellos, ya fueron usados en el ejercicio del capítulo 1.

### 7.1. BindValueProcessor. ###

Este procesador se usa para crear un *double binding* entre un *glue attribute* y la propiedad 'value' de un elemento HTML. Se debe emplear sobre elementos que hagan uso de esta propiedad como por ejemplo `input` o `select`, ya que en otros casos no tendría sentido.

Este procesador, recorrerá todos los elementos de la vista del componente que se esté procesando. Cuando sea encontrado un elemento que disponga del atributo `gphp-bind-value="%G_ATTR%"` o `data-gphp-bind-value="%G_ATTR%"`, será sobre dicho elemento donde se cree el *double binding* desde su propiedad 'value' con la del *glue attribute* de nombre '%G_ATTR%'.

>Tal y como usted supone, %G_ATTR% representa el nombre de un *glue attribute*.

### 7.2. BindHtmlProcessor. ###

Este procesador se usa para crear un *binding* simple desde un *glue attribute* hasta el HTML interno del elemento. El mismo se especifica con el atributo `gphp-bind-html="%G_ATTR%"` o `data-gphp-bind-html="%G_ATTR%"`.

En el ejercicio del capítulo 1 se empleó este procesador en el componente Label.

### 7.3. BindAttributesProcessor. ###

Este procesador sirve para crear un *binding* simple desde un *glue attribute* hasta un atributo del elemento HTML que lo contenga. El mismo se especifica con el atributo `gphp-bind-attr-%ATTR%="%G_ATTR%"` o `data-gphp-bind-attr-%ATTR%="%G_ATTR%"`.

Por ejemplo, en el siguiente caso, el atributo 'class' del elemento 'div' mantendrá el *binding* con el *glue attribute* 'class':

`<div gphp-bind-attr-class="class"></div>`

### 7.4. BindEventsProcessor. ###

Este procesador se usa para indicar eventos del componente desde elementos de la vista. Se indica con el atributo `gphp-bind-events="%EV_1% %EV_2% ..."` o
`data-gphp-bind-events="%EV_1% %EV_2% ..."`.

Es de destacar que se pueden indicar varios eventos si los nombres se separan por espacios. En el siguiente ejemplo, los eventos 'click' y 'mousedown' del elemento `<button>` producirán eventos de igual nombre en el componente.

`<button gphp-bind-events="click mousedown">Click Me!</button>`

### 7.5. ShortEventsProcessor. ###

Este procesador también sirve para especificar un único evento del componente desde un elemento de la vista. Se especifica con dos arrobas seguido del nombre del evento.

En el siguiente ejemplo se ha especificado que el evento 'click' del elemento `<button>` producirá el evento de igual nombre en el componente:

`<button @@click>Click Me!</button>`
