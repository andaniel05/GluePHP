# Capítulo 6. Trabajando con sidebars. #

Como vimos en el capítulo 1, un *sidebar* no es más que una sección dentro del código HTML o vista de la página donde se muestran las vistas de los componentes insertados en la misma. Cuando creamos la clase App mostramos que la forma de mostrar un *sidebar* era a través del método `renderSidebar(string $id)`. En aquel momento comentamos que en la clase `Andaniel05\GluePHP\AbstractApp` existía por defecto un único *sidebar* de nombre 'body' y que más adelante se mostraría la forma de crearlos.

La manera de crear los *sidebars* de una página es muy sencilla. Para ello solo hay que crear un método público llamado 'sidebars' y hacerlo devolver un *array* donde sus elementos se corresponderá con los nombres de los *sidebars* que existirán en la página.

En el siguiente fragmento de código se ha declarado la existencia de los *sidebars* 'header', 'body' y 'footer' en la página.

```php
class App extends AbstractApp
{
    public function sidebars(): array
    {
        return ['header', 'body', 'footer'];
    }

    // ...
}
```

Adicionalmente existe una manera de insertar componentes en los *sidebars* a la vez que estos se declaran. En el siguiente ejemplo se inserta un componente de tipo Logo en el sidebar 'header'.

```php
class App extends AbstractApp
{
    public function sidebars(): array
    {
        return [
            'header' => [
                new Logo,
            ],
            'body', 'footer'
        ];
    }

    // ...
}
```
