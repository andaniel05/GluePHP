# Capítulo 9. Profundizando en la creación de componentes. #

En el capítulo 1 se mostró la forma de crear tipos de componentes para GluePHP. Recordemos que la tarea consiste de los siguientes pasos:

1. Crear una clase descendiente de `Andaniel05\GluePHP\Component\AbstractComponent`.
2. Declarar los *glue attributes* especificando la anotación `@Glue` sobre los respectivos atributos de la clase.
3. Declarar funcionalidades especiales(*binding* y emisión de eventos) de ciertos elementos de la vista.

## 1. Personalizando los *getters* y *setters* de los *glue attributes*. ##

Tal y como se explicó en el capítulo 1, por cada *glue attribute* existente en la clase del componente, va a existir un método tipo *getter* y otro *setter* para el mismo.

