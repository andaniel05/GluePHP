# Capítulo 7. Trabajando con assets. #

En el desarrollo web se les conoce como *assets* a todos los recursos referenciados desde el código HTML de las páginas como son las hojas de estilo, *scripts*, etc. Dado que la carga de las páginas implica también la carga de sus recursos, es muy importante tener en cuenta la cantidad y tamaño de los mismos para garantizar que el proceso esté lo más optimizado posible.

En las aplicaciones GluePHP los *assets* se definen tanto en la clase de la aplicación como en la de los componentes, y aunque en ambos casos se hace de manera similar su tratamiento es diferente. Cuando estos son declarados en la clase de la aplicación significa que los mismos siempre se incluirán en la página, en cambio, cuando se declaran en la clase de un componente solo se incluirán si existe en la aplicación al menos un componente de ese tipo. De esta manera se garantiza que las aplicaciones contengan solo los *assets* que necesitan.

>En esta versión todavía no se encuentra soportada la gestión dinámica de *assets* lo que quiere decir que después de la carga de la página no se añadirá ningún nuevo *asset* independientemente de que se inserte algún nuevo componente con dependencias de *assets* que no se encuentren en la página.

GluePHP trata a los *assets* como objetos donde sus respectivas clases serán hijas de la clase `Andaniel05\ComposedViews\Asset\AbstractAsset`. De esta manera, todas las instancias contarán con un identificador y un mecanismo para soportar dependencias y grupos. Además, teniendo en cuenta que los *assets* se necesitan imprimir en la página y que por lo general su vista solo se compone de un único elemento HTML, contarán con una instancia de la clase `Andaniel05\ComposedViews\HtmlElement\HtmlElement` que será la encargada de generar la vista del *asset*. De esta manera el usuario podrá editar los atributos del elemento si lo desea. Teniendo en cuenta además que por lo general cada *asset* solo se imprime una única vez en la página, contarán con una bandera para indicar si el mismo ya ha sido impreso o no.

## 1. Imprimiendo los assets. ##

Como los *assets* forman parte del código HTML o vistas de las páginas, será en las clases de las aplicaciones donde se deberán mostrarán.

Para imprimir todos los *assets* de un grupo existe el método `renderAssets(?string $groups = null, bool $filterUnused = true, bool $markUsage = true): string`. Como puede ver el método acepta varios argumentos opcionales ya que presentan valores por defecto. Con el primer argumento `$groups` indica que se deben imprimir solo los *assets* que pertenezcan a los grupos separados por espacios. En el caso de que su valor sea nulo se imprimirán todos los *assets* sin importan los grupos a los que pertenezcan. Cuando el argumento `$filterUnused` sea verdadero se indicará que solo se imprimirán los *assets* que no hayan sido impresos todavía y en el caso de que su valor sea falso significa que su uso no se tendrá en cuenta. Cuando el argumento `$markUsage` sea verdadero todos los *assets* que se impriman serán marcados como usados. Es muy importante destacar que esta función imprimirá los *assets* de forma ordenada por lo que tendrá en cuenta sus dependencias. Cuando un *asset* depende de otro será impreso después de su dependencia.

Otro método del que se dispone para la impresión de *assets* es `renderAsset(string $assetId, bool $required = true, bool $markUsage = true): string`. El mismo sirve para imprimir un único *asset* especificando su identificador. Cuando el argumento `$required` sea verdadero se lanzará una excepción del tipo `Andaniel05\ComposedViews\Exception\AssetNotFoundException` en el caso de que no exista ningún *asset* con el identificador especificado. De igual forma el argumento `$markUsage` sirve para marcar o no su uso después de su impresión.

## 2. Declarando assets. ##

Para declarar *assets* tanto en la clase de la aplicación como en la de los componentes, es necesario crear un método público de nombre 'assets' que devuelva un *array* con las instancias de los mismos.

Para soportar los tipos de *assets* más comunes existen predefinidas una serie de clases que han sido diseñadas de forma tal que a través de sus constructores se les pueda proporcionar los datos necesarios por orden de importancia. El primer argumento se va a corresponder siempre con el identificador del *asset*. El segundo con los datos del tipo correspondiente ya sea una URI o un fragmento de código JavaScript o CSS. El tercero y cuarto serán opcionales y especificarán las dependencias y grupos respectivamente mediante un `string` donde se interpretarán múltiples valores separados por espacios. Es importante mencionar que todas las clases predefinidas pertenecen a un grupo determinado.

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

En la clase DatePicker se han declarado los *assets* 'datepicker-css' y 'datepicker-js' donde en ambos casos pertenecen al grupo 'plugins'. En el caso de 'datepicker-css' se ha declarado que depende solo de 'bootstrap-css' mientras en el caso de 'datepicker-js' dependerá 'jquery' y 'bootstrap-js'.

Como se comentó anteriormente, con un cuarto argumento de tipo *string* se podrán especificar grupos donde sus nombres se encontrarán separados por espacios.

## 3. Conociendo los tipos de assets predefinidos. ##

### Andaniel05\ComposedViews\Asset\ScriptAsset
