# Capítulo 2. Usando closures para la definición de eventos. #

Las funciones anónimas, más conocidas en PHP como *closures*, constituyen un potente recurso de programación presente hoy en día en la mayoría de los lenguajes modernos, donde una de sus aplicaciones más típicas se encuentra precisamente en la vinculación a eventos.

En el capítulo anterior comentábamos que se aceptaba cualquier tipo de *callback* como función manejadora de eventos, pero para el caso de los *closures* podía ser necesario un tratamiento especial. Durante este capítulo vamos a profundizar en el tema teniendo en cuenta las consideraciones necesarias al respecto.

>En el archivo [app2.zip](https://github.com/andaniel05/GluePHP/raw/0.1a/doc/res/Cap2/app2.zip) encontrará resuelto el ejercicio de este capítulo.

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

Como puede notar, de esta forma, hemos implementado la misma lógica pero de una forma mucho más compacta.

Si tras esta modificación procedemos a ejecutar la app nuevamente, el resultado obtenido no será el esperado ya que se habrá producido un error del tipo "Serialization of 'Closure' is not allowed". Esta es una desventaja que presentan los *closures* en PHP y es que de manera nativa no se permite su serialización.

En este punto, usted se puede preguntar por qué la existencia de este error si hasta el momento no hemos ejecutado ninguna serialización de manera directa. Recordemos que en nuestros controladores realizamos la persistencia de la aplicación a través de la sesión(`$_SESSION['app'] = $app;`). Como lo que se intenta guardar es un objeto, el motor de PHP intentará serializarlo para después guardarlo como cadena, y este es, precisamente la causa del error.

## 2. Usando "Opis/Closure" para la persistencia. ##

Afortunadamente existen algunas soluciones de terceros para resolver este problema. Una de las más populares y completas la constituye el proyecto 'Opis/Closure' el cuál recomendamos usar al respecto.

Primeramente debe realizar la instalación del paquete mediante Composer.

    $ composer require opis/closure ^3.0.11

Una vez que la instalación ha finalizado debemos hacer los respectivos ajustes a los controladores.

### Modificando el controlador de carga. ###

Edite el archivo *index.php* de la siguiente manera:

```php
<?php

require_once 'bootstrap.php';

use function Opis\Closure\serialize as s;

// Se instancia la app con sus componentes y eventos.
$app = require_once 'app.php';

// Antes de persistir la app es necesario esta sentencia.
$app->setBooted(true);

// Se persiste la instancia de la app donde en este caso la persistencia se hace
// mediante la sesión.
session_start();
$_SESSION['app'] = s($app);

// Se imprime en el navegador el código HTML de la página.
$app->print();
```

### Modificando el controlador de procesamiento. ###

Edite el archivo *process.php* de la siguiente manera:

```php
<?php

require_once 'bootstrap.php';

use Andaniel05\GluePHP\Request\Request;
use function Opis\Closure\{serialize as s, unserialize as u};

// Obtiene la instancia de la app persistida por el controlador de carga o por
// el procesamiento anterior.
session_start();
$app = u($_SESSION['app']);

// La app procesa la solicitud y devuelve una respuesta.
$request = Request::createFromJSON($_REQUEST['glue_request']);
$response = $app->handle($request);

// Hack para corregir un bug de opis. Será eliminado próximamente.
(function ($dispatcher) {
    $dispatcher->sorted = [];
})->call($app->getDispatcher());

// Vuelve a persistir la app.
$_SESSION['app'] = s($app);

// Envía al navegador la respuesta en formato JSON.
echo $response->toJSON();
die();

```

Note la presencia de las funciones `s()` y `u()` en las operaciones de lectura y escritura en la sesión.

Después de realizar estas operaciones podemos ejecutar nuevamente la app y comprobaremos el correcto funcionamiento de la misma.

## 3. Consideraciones. ##

Cuando se trabaja con *closures* es necesario tener en cuenta el método usado en los controladores para las operaciones de persistencia. Si el método se basa en la serialización nativa de PHP, deberá emplear alguna alternativa que soporte la serialización de *closures*. Debe tener en cuenta que la mayoría de estas alternativas presentan un rendimiento menor que si se emplea la serialización nativa, no obstante, en el caso de Opis/Closure su rendimiento llega a ser bastante aceptable.
