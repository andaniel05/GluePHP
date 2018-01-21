# Capítulo 13. Funcionamiento de una glue app #

El funcionamiento de una *glue app* sucede en una etapa inicial llamada **carga** y en muchas otras donde cada una de ellas se nombra **procesamiento**.

## Carga: ##

La carga se produce cuando el navegador accede a la URI que contiene la vista(HTML) de la página única. Durante esta etapa se produce el siguiente detalle de operaciones entre el navegador web y el servidor:

1. Navegador: Accede a la URI de la app.
2. Servidor: Instancia la app con sus componentes.
3. Servidor: Define la lógica de los eventos.
4. Servidor: Persiste la instancia de la app.
5. Servidor: Responde al navegador enviando el código HTML de la app.
6. Navegador: Instancia la app con sus componentes.
7. Navegador: Procesa los componentes.

En esta etapa es necesario que el servidor persista la instancia de la app ya que esta será usada nuevamente durante la próxima etapa de procesamiento. Como las instancias de los componentes forman parte de la instancia de la app, estas también son persistidas.

Por otra parte, puede notar que se crean instancias de la app y sus componentes primero en el servidor y después en el navegador. Antes habíamos mencionado que las *glue apps* mantenían datos compartidos y sincronizados entre el navegador y el servidor, y este es, precisamente el objetivo de crear una instancia equivalente por cada entorno. Cuando en el paso 4 se envía el código HTML al navegador, embedido con este, existe además un fragmento de código JavaScript que entre otras funcionalidades define las clases de los componentes, crea las instancias y las inicializa.

El último paso de la etapa consiste en procesar los componentes en el navegador. Esto no es más que aplicarle al componente frontend recién creado un determinado algoritmo, pero sobre este tema profundizaremos más adelante.

## Procesamiento: ##

Una vez que la carga ha finalizado la app está totalmente operativa por lo que el usuario comenzará a interactuar con la misma. Esto provocará que se disparen ciertos eventos en determinados componentes como por ejemplo, el hacer clic en un botón, modificar un campo de texto, etc. Como la lógica de los eventos fue programada en el servidor(Paso 3 de la etapa de carga), será necesario llevar a este tanto los datos del evento en curso, como los cambios producidos hasta el momento en los componentes frontend. El detalle de operaciones de esta etapa es el siguiente:

1. Navegador: Envía una solicitud ajax al servidor con los datos del evento y de los nuevos cambios en los componentes frontend.
2. Servidor: Obtiene la instancia de la app persistida anteriormente.
3. Servidor: Actualiza los componentes backend con los cambios recibidos en la solicitud actual.
4. Servidor: Procesa el evento. Generalmente provoca envío de acciones al navegador.
5. Servidor: Persiste la instancia de la app con sus componentes.
6. Servidor: Responde al navegador.
7. Navegador: Procesa la respuesta.

Una vez que el servidor recibe una solicitud de que se debe procesar un determinado evento, este obtiene inmediatamente la instancia backend de la app persistida durante la etapa de carga o durante la etapa de procesamiento anterior. Como los componentes backend y frontend tienen que estar sincronizados la primera tarea que se realiza es la actualización de los componentes backend con los cambios que se produjeron en el frontend.

Seguidamente se procede a ejecutar la lógica del evento lo que generalmente ocasiona modificaciones en determinados componentes backend. Si durante el procesamiento de un evento, se produce alguna modificación de algún componente, inmediatamente se envía una acción al navegador con información del cambio para que este actualice lo antes posible dicho componente frontend. Enviar una acción no es más que escribir en la salida al navegador datos en formato JSON, lo que provocará que la actual solicitud ajax pase a ser de tipo *streaming* ya que se comienza a enviar datos de respuesta sin que esto finalice la misma.

Después de que se ha ejecutado la lógica del evento, se tiene que volver a persistir la app con sus componentes para que la próxima etapa de procesamiento cuente con las modificaciones que se produjeron durante la actual etapa.

Por último el servidor termina la solicitud actual enviando cierta información del proceso que termina siendo interpretada y procesada en el navegador.