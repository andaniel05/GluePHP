# Capítulo 2. Usando closures para la definición de eventos. #

Las funciones anónimas, más conocidas en PHP como *closures*, constituyen un potente recurso de programación presente hoy en día en la mayoría de los lenguajes modernos, donde una de sus aplicaciones más típicas se encuentra precisamente en la vinculación a eventos.

En el capítulo anterior comentábamos que se aceptaba cualquier tipo de *callback* como función manejadora de eventos, pero para el caso de los *closures* podía ser necesario un tratamiento especial. Durante este capítulo vamos a profundizar en el tema teniendo en cuenta las consideraciones al respecto.

>En el archivo [app2.zip](https://github.com/andaniel05/GluePHP/raw/0.1a/doc/res/Cap2/app2.zip) encontrará resuelto el ejercicio de este capítulo.

## 1. Definiendo la lógica de los eventos mediante closures. ##

Cuando anteriormente declaramos el evento 'click' del botón, lo hicimos proporcionando el nombre de la función 'clickButton' como función manejadora, donde más tarde creamos e implementamos dicha función.

Modifique la declaración de eventos del archivo *app.php* de la siguiente forma:

```php

// ...

////////////////////////////
// Declaración de eventos //
////////////////////////////

// Declara y define en el mismo lugar el evento 'click' del botón.
$button->on('click', function () use ($input, $label) {
    $label->setText('Hola ' . $input->getText());
});

return $app;
```

>La función 'clickButton' que anteriormente definimos en el archivo *bootstrap.php* ya no es necesaria. Si lo desea puede eliminarla o simplemente ignorarla.

Si tras esta modificación procedemos a ejecutar la app nuevamente nos encontraremos con un error fatal del tipo "Serialization of 'Closure' is not allowed".
