# Capítulo 2. Usando closures para la definición de eventos. #

>Actualmente este capítulo se encuentra incompleto pues depende de la realización de otro proyecto. De cualquier manera recomendamos su lectura ya que el uso de *closures* para la vinculación de eventos constituye la manera más natural de definirlos.

Las funciones anónimas, más conocidas en PHP como *closures*, constituyen un potente recurso de programación presente hoy en día en la mayoría de los lenguajes modernos, donde una de sus aplicaciones más típicas se encuentra precisamente en la vinculación a eventos.

En el capítulo anterior comentábamos que se aceptaba cualquier tipo de *callback* como función manejadora de eventos, pero para el caso de los *closures* podía ser necesario un tratamiento especial. Durante este capítulo vamos a profundizar en el tema teniendo en cuenta las consideraciones necesarias al respecto.

## 1. Vinculando y definiendo eventos en el mismo lugar. ##

En la vinculación de eventos anterior, declaramos que la función 'clickButton' sería la responsable de manejar el evento 'click' del botón. Para su implementación, tuvimos que crear dicha función dentro de la cual fué necesario referenciar nuevamente a los componentes requeridos para su lógica.

Dado que por lo general, las funciones manejadoras de eventos solo son llamadas cuando se disparan sus respectivos eventos, no tiene mucho sentido darles un nombre para que puedan ser llamadas desde otras partes del código. Gracias a que en PHP existen las funciones anónimas o *closures*, emplearlas para la vinculación de los eventos resulta la forma más natural de hacerlo ya que además, pueden heredar muy fácilmente variables desde su ámbito padre.

Modifique la vinculación de eventos del archivo *app.php* de la siguiente forma:

```php

// ...

////////////////////////////
// Vinculación de eventos //
////////////////////////////

// Vincula y define en el mismo lugar el evento 'click' del botón.
$button->on('click', function () use ($input, $label) {
    $label->setText('Hola ' . $input->getText());
});

return $app;
```

>La función 'clickButton' existente en el archivo *bootstrap.php* ya no es necesaria. Si lo desea puede eliminarla o simplemente ignorarla.

Como puede notar, de esta forma, hemos implementado la misma lógica pero de una forma mucho más compacta y natural.

Si tras esta modificación procedemos a ejecutar la app nuevamente, el resultado obtenido no será el esperado ya que se habrá producido un error del tipo "Serialization of 'Closure' is not allowed". Esta es una desventaja que presentan los *closures* en PHP y es que de manera nativa no se permite su serialización.

En este punto, usted se puede preguntar por qué la existencia de este error si hasta el momento no hemos ejecutado ninguna serialización de manera directa. Recordemos que en nuestros controladores realizamos la persistencia de la aplicación a través de la sesión(`$_SESSION['app'] = $app;`). Como lo que se intenta guardar es un objeto, el motor de PHP intentará serializarlo para después guardarlo como cadena, y este es, precisamente la causa del error.

>Afortunadamente existen algunas técnicas para resolver este problema. Actualmente estamos desarrollando un componente independiente que podrá ser empleado en las operaciones de persistencia. Dicho componente solucionará este error y al emplear un algoritmo más personalizado esperamos obtener una mejora en el rendimiento.

>Por el momento basta con que sepa que el uso de *closures* lo tenemos muy presente para el desarrollo con GluePHP.
