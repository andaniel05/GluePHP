<?php
/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */

require_once __DIR__ . '/../bootstrap.php';

use function Opis\Closure\serialize as s;

$app = require_once $_GET['app'];

if ($_GET['saveInSession'] == true) {
    session_start();
    $app->setBooted(true);
    $_SESSION['app'] = s($app);
}

$app->print();
