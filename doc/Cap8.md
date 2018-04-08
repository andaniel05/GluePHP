# Capítulo 8. Estructura jerárquica de los componentes. #

En GluePHP todos los componentes presentan una estructura jerárquica ya que pueden contener un padre y múltiples hijos. Esta estructura representa además una estructura de árbol y este capítulo tiene como objetivo mostrar todas las operaciones relacionadas con la misma.

>Es importante mencionar que todas las operaciones mostradas en el capítulo son aplicables también sobre las instancias de las aplicaciones.

## 1. El componente padre. ##

Respecto al componente padre solo van a existir operaciones tipo *getter* y *setter*. Para obtenerlo basta con llamar a `$component->getParent();` mientras que para establecerlo se debe invocar al método `$component->setParent($parent);`. Cuando a un componente se le establece un padre, por defecto el padre lo añade como su hijo ya que es algo totalmente lógico. No obstante, si por algún motivo no se desea ese comportamiento adicional, la operación se debe realizar de la siguiente manera: `$component->setParent($parent, false);`.

La manera más sencilla de borrar el padre de un componente es a través de la función `$component->detach();`.

## 2. Los componentes hijos. ##

Para añadir un hijo a un componente se debe llamar a la función `$component->addChild($child);`. De manera equivalente, cuando un componente añade un hijo, sobre el segundo se va a asignar al primero como su padre. Si esto no se desea se debe realizar la llamada de la siguiente manera: `$component->addChild($child, false);`.

Para buscar hijos se usa la función `$component->getComponent('id');` donde el argumento representa el identificador del componente a buscar. Si el componente es encontrado se devolverá la instancia del mismo mientras que en caso contrario se devolverá `NULL`. Es muy importante destacar que esta función hará una búsqueda en profundidad sobre todo el árbol, es decir, si el componente no presenta ningún hijo directo con el identificador buscado, la búsqueda continuará sobre sus hijos y los hijos de sus hijos respectivamente hasta hallar alguna coincidencia.

Para obtener todos los hijos directos se debe llamar a la función `$component->getChildren()` la cual devolverá un *array* asociativo donde las claves serán los identificadores mientras que los valores serán las instancias. Si se desea consultar si un componente es hijo directo de otro, se puede hacer la llamada `$component->hasRootChild('id');` la cual devolverá un valor booleano según el caso.

Para eliminar un hijo existe la función `$component->dropChild('id');`. Esta función solo trabaja sobre los hijos directos y cuando uno es encontrado con igual identificador, a este se le establecerá su padre a `NULL`. Tal y como se ha mostrado hasta ahora, si ese comportamiento adicional se desea deshabilitar se debe hacer especificando 'false' como segundo argumento: `$component->dropChild('id', false);`.

## 3. Iterando sobre el árbol. ##

En algunas ocasiones se necesitará recorrer en profundidad todos los componentes de un árbol. En ese caso debe emplear la función `traverse()` la cual devuelve un iterador comenzando desde el primer hijo directo hasta el último elemento del árbol.

El siguiente ejemplo ayuda a comprender el funcionamiento:

```
parent
   |
   |____ comp1
   |         |
   |         |____ comp2
   |                  |
   |                  |____ comp3
   |                  |
   |                  |____ comp4
   |
   |___ comp5
```

```php
foreach ($parent->traverse() as $comp) {
    echo $comp->getId() . PHP_EOL;
}
```

El *script* anterior imprimirá en pantalla los nombres de los componentes en el orden mostrado, es decir:

```
comp1
comp2
comp3
comp4
comp5
```

## 4. Clonando un árbol de componentes. ##

Cuando necesite clonar un componente **se tiene que hacer** de la siguiente manera: `$component2 = $component1->copy();`.

>Es muy importante aclarar que **no se recomienda emplear la clonación nativa de PHP** ya que el resultado no se ajusta a los requisitos de GluePHP.

## 5. Eventos en el árbol de componentes de las páginas. ##

En una *glue app*, el árbol de componentes principal lo constituye el de las páginas ya que son estas quienes contienen a los componentes. Cuando en cualquier parte del mismo, se produzca tanto una inserción como alguna eliminación, se producirá sobre la página un evento antes y otro después de la respectiva operación. Estos eventos son usados internamente por GluePHP, pero adicionalmente pueden ser usados por los usuarios para incluir cualquier tipo de lógica.

```php
use Andaniel05\ComposedViews\PageEvents;
use Andaniel05\ComposedViews\Event\{BeforeInsertionEvent, AfterInsertionEvent, BeforeDeletionEvent, AfterDeletionEvent};

$app->on(PageEvents::BEFORE_INSERTION, 'beforeInsertionHandler');
$app->on(PageEvents::AFTER_INSERTION, 'afterInsertionHandler');
$app->on(PageEvents::BEFORE_DELETION, 'beforeDeletionHandler');
$app->on(PageEvents::AFTER_DELETION, 'afterDeletionHandler');

function beforeInsertionHandler(BeforeInsertionEvent $event)
{
    $child = $event->getChild();
    $parent = $event->getParent();

    $event->cancel();
}

function afterInsertionHandler(AfterInsertionEvent $event)
{
}

function beforeDeletionHandler(BeforeDeletionEvent $event)
{
}

function afterDeletionHandler(AfterDeletionEvent $event)
{
}
```

El fragmento anterior muestra la forma de usar esos eventos así como las clases a usar según el tipo. En todos los casos se contará con información sobre el componente padre e hijo pero solo los de tipo antes podrán ser cancelados.
