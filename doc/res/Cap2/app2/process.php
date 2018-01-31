<?php

require_once 'bootstrap.php';

use Andaniel05\GluePHP\Request\Request;

// Obtiene la instancia de la app persistida por el controlador de carga o por
// el procesamiento anterior.
session_start();
$app = $_SESSION['app'];

// La app procesa la solicitud y devuelve una respuesta.
$request = Request::createFromJSON($_REQUEST['glue_request']);
$response = $app->handle($request);

// Vuelve a persistir la app.
$_SESSION['app'] = $app;

// Envía al navegador la respuesta en formato JSON.
echo $response->toJSON();
die();