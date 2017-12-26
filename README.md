## Introducción ##

GluePHP es un componente framework para PHP 7.1 que facilita el desarrollo de aplicaciones web de una sola página mediante el paradigma de la programación dirigida por eventos.

Es la primera implementación de la arquitectura [*Glue Apps*](http://#) y tiene además el objetivo de definir muy bien sus partes para lograr la versión 1.0 de la misma.
 
### ¿Por qué es un componente framework? ###

Puede ser integrado con otros frameworks PHP de propósito general ya que su alcance radica solo en la capa de la interfaz de usuario.

### ¿Qué es una aplicación web de una sola página? ###

Son aplicaciones web dinámicas donde el navegador carga la página una única vez y el resto de la comunicación con el servidor se produce de forma asíncrona(AJAX). 

[https://es.wikipedia.org/wiki/Single-page_application](https://es.wikipedia.org/wiki/Single-page_application)

### ¿Qué es una aplicación dirigida por eventos? ###

Las aplicaciones con interfaces gráficas([GUI](http://#)) son, en su mayoría, aplicaciones dirigidas por eventos. Seguramente ya esté familiarizado con el desarrollo de aplicaciones para el desktop o para móviles, por lo que esta filosofía le resultará familiar.

[https://es.wikipedia.org/wiki/Programaci%C3%B3n_dirigida_por_eventos](https://es.wikipedia.org/wiki/Programaci%C3%B3n_dirigida_por_eventos)

## Instalación ##

La instalación de GluePHP se realiza mediante composer. Para esto es necesario declarar las siguientes dependencias en el archivo composer.json 

	{
	    "require": {
	        "andaniel05/composed-views": "dev-0.1a",
			"andaniel05/glue-php": "dev-0.1a"
	    }
	}

Una vez hecho esto se debe ejecutar composer de la siguiente manera:

	$ composer update

# Tutoriales #

Con el objetivo de enseñar el uso de GluePHP hemos preparado una serie de tutoriales prácticos. Cada uno de ellos mostrará las diferentes características del framework por lo que una vez finalizados usted será capaz de adaptar lo aprendido en sus proyectos reales. Recomendamos cursarlos en el orden mostrado.

[1. Conceptos básicos.](doc/tutorial1/index.md)