# Capítulo 7. Trabajando con assets. #

En el desarrollo web se les conoce como *assets* a todos los recursos referenciados desde el código HTML de las páginas como son las hojas de estilo, *scripts*, imágenes, etc. Dado que la carga de las páginas implica también la carga de sus recursos, es muy importante tener en cuenta la cantidad y tamaño de los mismos para garantizar que el proceso esté lo más optimizado posible.

En las aplicaciones GluePHP los *assets* se definen tanto en la clase de la aplicación como en la de los componentes, y aunque en ambos casos se hace de manera similar su tratamiento es diferente. Cuando estos son declarados en la clase de la aplicación significa que los mismos siempre se incluirán en la página, en cambio, cuando se declaran en la clase de un componente solo se incluirán si existe en la aplicación al menos un componente de ese tipo. De esta manera se garantiza que las aplicaciones contengan solo los *assets* que necesitan.

>En esta versión todavía no se encuentra soportada la gestión dinámica de *assets* lo que quiere decir que después de la carga de la página no se añadirá ningún nuevo *asset* aunque se inserte en la misma algún nuevo componente con dependencias a *assets* que no se encuentren cargados.

GluePHP trata a los *assets* como objetos donde sus respectivas clases serán hijas de la clase `Andaniel05\ComposedViews\Asset\AbstractAsset`. De esta manera, todas las instancias contarán con un identificador y un mecanismo para soportar dependencias y grupos. Además, teniendo en cuenta que los *assets* se necesitan imprimir en la página y que por lo general su vista solo se compone de un único elemento HTML, esta clase desciende de la clase `Andaniel05\ComposedViews\HtmlElement\HtmlElement` para generar la vista del *asset*. De esta manera el usuario podrá editar los atributos del elemento si lo desea. Teniendo en cuenta además que por lo general cada *asset* solo se imprime una única vez en la página, contarán con una bandera para indicar si el mismo ya ha sido impreso o no.

## 1. Imprimiendo los assets. ##

Como los *assets* forman parte del código HTML o vistas de las páginas, será en las clases de las aplicaciones donde se deberán mostrarán.

Para imprimir todos los *assets* de un grupo existe el método `renderAssets(?string $groups = null, bool $filterUnused = true, bool $markUsage = true): string`. Como puede ver, el método acepta varios argumentos opcionales ya que presentan valores por defecto. Con el primer argumento `$groups` indica que se deben imprimir solo los *assets* que pertenezcan a los grupos separados por espacios. En el caso de que su valor sea nulo se imprimirán todos los *assets* sin importan los grupos a los que pertenezcan. Cuando el argumento `$filterUnused` sea verdadero se indicará que solo se imprimirán los *assets* que no hayan sido impresos todavía y en el caso de que su valor sea falso significa que su uso no se tendrá en cuenta. Cuando el argumento `$markUsage` sea verdadero todos los *assets* que se impriman serán marcados como usados. Es muy importante destacar que esta función imprimirá los *assets* de forma ordenada por lo que tendrá en cuenta sus dependencias. Cuando un *asset* depende de otro será impreso después de su dependencia.

Otro método del que se dispone para la impresión de *assets* es `renderAsset(string $assetId, bool $required = true, bool $markUsage = true): string`. El mismo sirve para imprimir un único *asset* especificando su identificador. Cuando el argumento `$required` sea verdadero se lanzará una excepción del tipo `Andaniel05\ComposedViews\Exception\AssetNotFoundException` en el caso de que no exista ningún *asset* con el identificador especificado. De igual forma el argumento `$markUsage` sirve para marcar o no su uso después de su impresión.

## 2. Declarando assets. ##

Para declarar *assets* tanto en la clase de la aplicación como en la de los componentes, es necesario crear un método público de nombre 'assets' que devuelva un *array* con las instancias de los mismos.

Para soportar los tipos de *assets* más comunes existen predefinidas una serie de clases que han sido diseñadas de forma tal que a través de sus constructores se les pueda proporcionar los datos necesarios por orden de importancia. El primer argumento se va a corresponder siempre con el identificador del *asset*. El segundo con los datos del tipo correspondiente ya sea una URI o un fragmento de código JavaScript o CSS. El tercero y cuarto serán opcionales y especificarán las dependencias y grupos respectivamente mediante un `string` donde se interpretarán múltiples valores separados por espacios. Es importante mencionar que todas las clases predefinidas pertenecen a uno o varios grupos por defecto.

En el siguiente ejemplo se han declarado *assets* de los tipos `Andaniel05\ComposedViews\Asset\{StyleAsset, ScriptAsset}`.

```php
use Andaniel05\ComposedViews\Asset\{StyleAsset, ScriptAsset};

class App extends AbstractApp
{
    public function assets(): array
    {
        return [
            new StyleAsset('bootstrap-css', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css'),
            new ScriptAsset('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js'),
            new ScriptAsset('bootstrap-js', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js', 'jquery'),
        ];
    }

    // ...
}

class DatePicker extends AbstractComponent
{
    public function assets(): array
    {
        return [
            'plugins' => [
                new StyleAsset('datepicker-css', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/css/bootstrap-datepicker.min.css', 'bootstrap-css'),
                new ScriptAsset('datepicker-js', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/js/bootstrap-datepicker.min.js', 'jquery bootstrap-js'),
            ],
        ];
    }

    // ...
}
```

En la clase App se han declarado los assets 'jquery', 'bootstrap-css' y 'bootstrap-js'. Tenga en cuenta que con los segundos argumentos se hace referencia a sus respectivas URIs y en el caso del *asset* 'bootstrap-js' con un tercer argumento se ha declarado que depende del *asset* 'jquery'.

En la clase DatePicker se han declarado los *assets* 'datepicker-css' y 'datepicker-js' donde en ambos casos pertenecen al grupo 'plugins'. En el caso de 'datepicker-css' se ha declarado que depende solo de 'bootstrap-css' mientras en el caso de 'datepicker-js' dependerá de 'jquery' y 'bootstrap-js'.

Como se comentó anteriormente, con un cuarto argumento de tipo *string* se podrán especificar uno o varios grupos separando sus nombres por espacios.

## 3. Conociendo los tipos de assets existentes. ##

La siguiente tabla muestra las clases existentes en GluePHP para trabajar con los tipos de *assets* más comunes. Todas se encuentran definidas en el espacio de nombres `Andaniel05\ComposedViews\Asset`. La tabla muestra el tipo de elemento HTML que representa el *asset*, los grupos y atributos predefinidos además de un ejemplo de su vista. El símbolo `%data%` se corresponde con el valor especificado por el usuario como segundo argumento del constructor.

Clase | Elemento | Atributos | Grupos | Vista
-- | -- | -- | -- | --
ScriptAsset | `script` | src="%data%" | scripts, uri | `<script src="%data%"></script>`
ContentScriptAsset | `script` | | scripts, content | `<script>%data%</script>`
StyleAsset | `link` | href="%data%", rel="stylesheet" | styles, uri | `<link href="%data%" rel="stylesheet">`
ContentStyleAsset | `style` | | styles, content | `<style>%data%</style>`
ImportAsset | `link` | href="%data%", rel="import" | imports, uri | `<link href="%data%" rel="import">`

## 4. Editando los assets. ##

La clase `Andaniel05\ComposedViews\HtmlElement\HtmlElement` se usa para generar la vista de un único elemento HTML. La misma solo se compone de cuatro datos que se corresponden con el tipo de elemento, los atributos, el contenido y la etiqueta de cierre. Todos esos se pueden editar a través de sus respectivos métodos de lectura y escritura. Dado que los *assets* son también instancias de esta clase su vista puede ser editada.

El siguiente ejemplo muestra como se añade un nuevo atributo a un *asset*.

```php
$bootstrapCss = new StyleAsset('bootstrap-css', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css');
$bootstrapCss->setAttribute('integrity', 'sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u');
echo $bootstrapCss->html();
```

El resultado de ese *script* será:

```html
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u">
```

## 5. Trabajando con rutas relativas. ##

Como ha podido ver hasta este momento solo hemos usados rutas absolutas para los *assets*. Dado que generalmente toda aplicación web usa *assets* alojados en su propio servidor, y teniendo en cuenta que la estructura de archivos es definida por el usuario, será necesario contar con una manera de trabajar con con las rutas relativas.

En la clase de la aplicación es posible especificar una ruta base como segundo argumento del constructor.

```php
$app = new App('process.php', 'http://127.0.0.1/public/');
```

Cuando se indica una ruta base en una aplicación lo más conveniente suele ser que la vista incluya una etiqueta `<base>` de HTML. De esta forma será el navegador quien combine de forma automática todas las rutas relativas con la ruta base. No obstante si se necesita generar manualmente una ruta que combine la ruta base con una ruta relativa se puede usar la función `basePath(string $assetUri = ''): string` como se muestra en el siguiente ejemplo:

```php
class App extends AbstractApp
{
    public function html(): ?string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>My glue app</title>
    <base href="{$this->basePath}">
</head>
<body>
    <img src="{$this->basePath('logo.png')}">
    {$this->renderSidebar('body')}

    {$this->renderAssets('scripts')}
</body>
</html>
HTML;
    }
}
```

## 6. Conociendo la superposición de assets. ##

Cuando una aplicación y uno o varios componentes definen un *asset* con un mismo identificador, será el de la aplicación el que prevalezca. Conociendo esto, cuando se necesite redefinir un *asset* definido en la clase de un componente que no se pueda editar, la solución puede ser declarar el *asset* en la aplicación como se ha mostrado anteriormente.

No obstante, dado que por lo general solo es necesario redefinir los *assets* para modificar su URI, existe una manera muy simplificada de hacerlo. La solución consiste en crear un método en la página de nombre `rewriteUri()` y hacerlo devolver un *array* asociativo donde sus llaves se corresponderán con el identificador del *asset* a modificar y su valor será la nueva URI.

En el ejemplo siguiente se ha reescrito la URI del *asset* 'jquery' para referirse a la versión 3.3.1. Esto quiere decir que cuando en la página se valla a imprimir este *asset* será esta URI la que se usará independientemente de los componentes que la definan de otra manera.

```php
class App extends AbstractApp
{
    public function rewriteUri(): array
    {
        return [
            'jquery' => 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js',
        ];
    }

    // ...
}
```

## 7. Filtrando assets. ##

Siempre que en las páginas se produzca alguna consulta de *assets*, antes de su devolución final se disparará internamente un evento que permitirá su filtrado. Gracias a este evento el usuario puede realizar operaciones personalizadas sobre los *assets*.

El siguiente ejemplo provocará que en la app nunca se usen los *assets* que posean una URI.

```php

use Andaniel05\ComposedViews\PageEvents;
use Andaniel05\ComposedViews\Event\FilterAssetsEvent;
use Andaniel05\ComposedViews\Asset\UriInterface;

$app->on(PageEvents::FILTER_ASSETS, 'filterAssets');

function filterAssets(FilterAssetsEvent $event)
{
    $assets = $event->getAssets();

    foreach ($assets as $id => $asset) {
        if ($asset instanceof UriInterface) {
            unset($assets[$id]);
        }
    }

    // Se establecen los assets que serán usados.
    $event->setAssets($assets);
}
```
