# GluePHP #

Bienvenido a GluePHP, un *framework* para el desarrollo de [aplicaciones web de una sola página](https://es.wikipedia.org/wiki/Single-page_application) empleando el [paradigma de la programación dirigida por eventos](https://es.wikipedia.org/wiki/Programaci%C3%B3n_dirigida_por_eventos).

### Requerimientos ###
- PHP 7.1

### Licencia ###
- MIT

## Instalación ##

>Tenga en cuenta que el proyecto se encuentra en una fase inestable.

La instalación de GluePHP se realiza mediante composer. Para esto es necesario declarar las siguientes dependencias en el archivo *composer.json*.

    {
        "require": {
            "andaniel05/composed-views": "dev-0.1a",
            "andaniel05/glue-php": "dev-0.1a"
        }
    }

Seguidamente se debe ejecutar el comando:

    $ composer update

## Terminología ##

Las aplicaciones hechas con GluePHP las definimos como **glue apps**. Una de las características de estas aplicaciones es que son compuestas, por lo que a sus componentes les llamamos **glue components**. Éstos componentes presentan datos compartidos y sincronizados entre el navegador y el servidor a los cuales definimos como **glue attributes**. Por otra parte, definimos como **glue kit** a un conjunto de *glue components* reutilizables.

Durante el desarrollo práctico del libro podrá comprender a fondo estos conceptos. No obstante, queremos aclarar que éstos solo tienen un significado local a nuestro proyecto y no guardan relación con otros ya bien conocidos en la informática como *glue code*, *glue framework*, etc.

Por razones de simplicidad, a lo largo del libro usaremos de manera equivalente los términos app, componente y kit, para referirnos a *glue app*, *glue component* y *glue kit* respectivamente.

## Filosofía ##

Desarrollar una *glue app* es muy similar a desarrollar una aplicación con interfaz gráfica para el *desktop* o para móviles. En esos casos se cuenta con una serie de componentes gráficos que son añadidos al diseño de la interfaz donde más tarde se les programa sus eventos. Esta filosofía de desarrollo se corresponde con el paradigma de la programación dirigida por eventos y es la empleada en el desarrollo con GluePHP.

Dada la gran diversidad que existe en el aspecto de las aplicaciones web, a la hora de desarrollar una *glue app* suele ser común tener que crear primeramente los componentes gráficos denominados *glue componentes* según nuestra terminología. No obstante, gracias a las facilidades que brinda GluePHP esta tarea se realiza de una manera muy sencilla.

## Ventajas y desventajas. ##

GluePHP facilita muchos de los esfuerzos necesarios para el desarrollo de aplicaciones web de una sola página en PHP. Es por esto que reduce considerablemente los tiempos de desarrollo sobre todo en las aplicaciones basadas en kits de componentes reutilizables como son las administraciones o aplicaciones de gestión en general.

No obstante, en aplicaciones con gran cantidad de componentes y/o alta concurrencia de eventos puede verse afectado el rendimiento.

## Libro de GluePHP. ##

- [Capítulo 1. Creando una glue app.](doc/Cap1.md)
- [Capítulo 2. Usando closures para la definición de eventos.](doc/Cap2.md)
- [Capítulo 3. Uso dinámico de componentes.](doc/Cap3.md)
- [Capítulo 4. Acciones.](doc/Cap4.md)
- [Capítulo 5. Trabajando con datos de los eventos frontend.]
- [Capítulo 6. Profundizando en la creación páginas.]
- [Capítulo 7. Profundizando en la creación de componentes.]
- [Capítulo 8. Procesadores.]
- [Capítulo 9. Integración con VueJS.]
- [Capítulo 10. Integración con Polymer.]
- [Capítulo 11. Creando kits de componentes.]
- [Capítulo 12. Trabajando con el frontend.]
- [Capítulo 13. Funcionamiento de una glue app.](doc/Cap13.md)

## Contribuyendo. ##

El desarrollo de GluePHP está basado en la metodología de [desarrollo guiado por pruebas(TDD)](https://es.wikipedia.org/wiki/Desarrollo_guiado_por_pruebas), por lo que cada funcionalidad del *framework* se encuentra cubierta por al menos una prueba. Para las pruebas al código PHP se emplea [PHPUnit](https://phpunit.de/) mientras que para el código JavaScript se emplea [MochaJS](https://mochajs.org/), [Chai](http://chaijs.com/) y [SinonJS](http://sinonjs.org/). Para las pruebas a las funcionalidades *full-stack* se emplean las tecnologías [PHPUnit](https://phpunit.de/), [Selenium Server](http://www.seleniumhq.org/) con [Chrome Driver](https://sites.google.com/a/chromium.org/chromedriver/).

El código JavaScript está basado mayormente en ES5 con el objetivo de lograr compatibilidad con la mayoría de navegadores posibles, no obstante, también se han empleado algunas funcionalidades de ES6 pero que se encuentran ampliamente soportadas.

Para el código JavaScript existen algunas tareas automatizadas con [GulpJS](https://gulpjs.com/) por lo que antes de hacer alguna modificación debe ejecutar el comando:

    $ gulp

### Pasos para contribuir en el proyecto. ###

1. Hacer un *fork* de este repositorio.
2. Clonar en local el nuevo repositorio que se ha creado en su cuenta de GitHub.
3. Realizar las modificaciones **con sus respectivas pruebas**.
4. Hacer *push* al origen.
5. Crear un *pull requests*.

### Ejecutando las pruebas. ###

Una vez que ha clonado localmente el repositorio debe realizar la instalación de las siguientes aplicaciones:

- [Composer](https://getcomposer.org/)
- [NPM](https://www.npmjs.com/)
- [Selenium Server](http://www.seleniumhq.org/)
- [Chrome Driver](https://sites.google.com/a/chromium.org/chromedriver/)
- [Java](https://www.java.com/es/download/)
- [Bower](https://bower.io/)

#### 1. Instale las dependencias de Composer.

    $ composer update

#### 2. Instale las dependencias de NPM.

    $ npm update

#### 3. Instale las dependencias de Bower

    $ bower install

#### 4. Ejecute el siguiente comando.

    $ php -S localhost:8085

#### 5. Ejecute Selenium Server.

    $ java -jar <ruta_al_archivo>/selenium-server-standalone-x.x.x.jar

#### 6. Ejecute PHPUnit.

    $ php vendor/phpunit/phpunit/phpunit

Tenga en cuenta que algunos antivirus pueden hacen fallar ciertas pruebas por lo que puede ser necesario que añada alguna excepción al respecto.
