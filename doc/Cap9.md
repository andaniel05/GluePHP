# Capítulo 9. Profundizando en la creación de componentes. #

En el capítulo 1 se mostró la forma de crear tipos de componentes para GluePHP. Recordemos que la tarea consiste de los siguientes pasos:

1. Crear una clase descendiente de `Andaniel05\GluePHP\Component\AbstractComponent`.
2. Declarar los *glue attributes* especificando la anotación `@Glue` sobre los respectivos atributos de la clase.
3. Declarar funcionalidades especiales(*binding* y emisión de eventos) de ciertos elementos de la vista.

## 1. Personalizando los *getters* y los *setters* de los *glue attributes*. ##

Tal y como se explicó en el capítulo 1, por cada *glue attribute* existente en la clase, existirán dos métodos para las operaciones *getters* y *setters*, donde sus nombres cumplirán con el formato *camelCase* y se compondrán de las palabras *get* o *set* según el caso seguido del nombre del *glue attribute*. Por defecto esto métodos poseen cierta lógica interna pero pueden ser redefinidos si se desea añadirles alguna lógica personalizada.

En el siguiente ejemplo se han definidos ambos métodos con el objetivo de soportar solo el tipo de datos *string*. En el caso del método *setter* es de destacar la presencia del segundo argumento `bool $sendAction = true` y de la sentencia `$this->_set('text', $text, $sendAction);`. La existencia de ambos tiene un carácter obligatorio en todo método *setter* redefinido ya que forman parte del uso interno dado por GluePHP.

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

## 2. Introducción a los procesadores. ##

Una de las piedras angulares más importantes en el funcionamiento interno de GluePHP lo constituyen los procesadores. Un procesador no es más que una función JavaScript que se aplica sobre una instancia recién creada de un componente *frontend*.

Cuando en el capítulo 1 nos desempeñamos con el rol del desarrollador de componentes, especificamos algunos atributos especiales sobre los elementos de las vistas para indicarle a GluePHP ciertas funcionalidades de los mismos como podía ser el *Data Binding* o la fuente de los eventos del componente. Toda esa magia y mucho más se logra gracias a los procesadores.

### 2.1. Especificando los procesadores ###
