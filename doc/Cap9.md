# Capítulo 9. Profundizando en la creación de componentes. #

En el capítulo 1 se mostró la forma de crear tipos de componentes para GluePHP. Recordemos que la tarea consiste de los siguientes pasos:

1. Crear una clase descendiente de `Andaniel05\GluePHP\Component\AbstractComponent`.
2. Declarar los *glue attributes* especificando la anotación `@Glue` sobre los respectivos atributos de la clase.
3. Declarar funcionalidades especiales(*binding* y emisión de eventos) de ciertos elementos de la vista.

## 1. Personalizando los *getters* y *setters* de los *glue attributes*. ##

Tal y como se explicó en el capítulo 1, por cada *glue attribute* existente en la clase existirán dos métodos para las operaciones *getters* y *setters* donde sus nombres cumplirán con el formato *camelCase* y se compondrán de las palabras *get* o *set* según el caso, seguido del nombre del *glue attribute*. Aunque por defecto ambos poseen cierta lógica interna, es posible redefinirlos para incluirle cierta lógica personalizada.

```php
class MyComponent extends AbstractComponent
{
    /**
     * @Glue(type="string")
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
