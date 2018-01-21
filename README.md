# GluePHP #

Bienvenido a GluePHP, un framework para el desarrollo de aplicaciones web de una sola página(SPA) empleando el paradigma de la programación dirigida por eventos.

### Requerimientos ###
- PHP 7.1

### Licencia ###
- MIT

## Instalación ##

La instalación de GluePHP se realiza mediante composer. Para esto es necesario declarar las siguientes dependencias en el archivo composer.json.

> Tenga en cuenta que el proyecto se encuentra aún en una fase inestable.

    {
        "require": {
            "andaniel05/composed-views": "dev-0.1a",
            "andaniel05/glue-php": "dev-0.1a"
        }
    }

Una vez hecho esto se debe ejecutar el comando:

    $ composer update

## Terminología ##

Las aplicaciones hechas con GluePHP las definimos como **glue apps**. Una de las características de estas aplicaciones es que son compuestas, por lo que a sus componentes les llamamos **glue components**. Éstos componentes presentan datos compartidos y sincronizados entre el frontend y el backend a los cuales definimos como **glue attributes**. Por otra parte, definimos como **glue kit** a un conjunto de *glue components* reutilizables.

Durante el desarrollo práctico del libro podrá comprender a fondo estos conceptos. No obstante, queremos aclarar que éstos solo tienen un significado local a nuestro proyecto y no guardan relación con otros ya bien conocidos en la informática como *glue code*, *glue framework*, etc.

A lo largo del libro y por razones de simplicidad usaremos además el término *app* para referirnos también a *glue app*. Ambos términos serán usados de forma indistinta. Además usaremos el término componente para referirnos sencillamente a un *glue component* y dado que por cada componente lógico existe una instancia en el navegador y otra en el servidor, a la primera nos referiremos como **componente frontend** mientras que al segundo como **componente backend**.

## Filosofía ##

El desarrollo de una *glue app* es muy similar al desarrollo de una aplicación con interfaz gráfica para el desktop o para móviles. En esos casos, se cuenta con kits de componentes gráficos que contienen botones, cajas de texto, calendarios, etc. Esos componentes se insertan en la vista(formulario, actividad, etc) y se les programa sus eventos.

Por lo general el desarrollo de una aplicación web suele partir de una plantilla HTML que define el aspecto de la misma. Al desarrollar una *glue app* partiendo de este escenario es necesario crear primeramente los *glue components*, tarea que se hace muy sencilla gracias a GluePHP. No obstante, si el desarrollo parte del uso de uno o varios *glue kits* esta tarea no será necesaria y se procede a programar la lógica de los eventos directamente.

## Libro de GluePHP. ##

- [Capítulo 1. Creando una glue app.](doc/Cap1.md)
- [Capítulo 2. Trabajando con closures.]
- [Capítulo 3. Acciones.]
- [Capítulo 4. Uso dinámico de componentes.]
- [Capítulo 5. Procesadores.]
- [Capítulo 6. Trabajando con los datos de los eventos.]
- [Capítulo 7. Profundizando en la creación páginas.]
- [Capítulo 8. Profundizando en la creación de componentes.]
- [Capítulo 9. Creando kits de componentes.]
- [Capítulo 10. Integración con VueJS.]
- [Capítulo 11. Integración con Polymer.]
- [Capítulo 12. Trabajando con el frontend.]
- [Capítulo 13. Funcionamiento de una glue app.](doc/Cap13.md)
