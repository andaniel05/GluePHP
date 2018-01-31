<?php

require_once 'bootstrap.php';

// Se instancia la app con sus componentes y eventos.
$app = require_once 'app.php';

// Antes de persistir la app es necesario esta sentencia.
$app->setBooted(true);

// Se persiste la instancia de la app donde en este caso la persistencia se hace
// mediante la sesión.
session_start();
$_SESSION['app'] = $app;

// Se imprime en el navegador el código HTML de la página.
$app->print();