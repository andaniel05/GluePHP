<?php

require_once __DIR__ . '/../bootstrap.php';
session_start();

use Andaniel05\GluePHP\Request\Request;
use function Opis\Closure\{serialize as s, unserialize as u};

$app = u($_SESSION['app']);

$request = Request::createFromJSON($_REQUEST['glue_request']);
$response = $app->handle($request);

$_SESSION['app'] = s($app);

echo $response->toJSON();
die();
