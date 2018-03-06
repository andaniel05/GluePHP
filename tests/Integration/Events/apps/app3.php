<?php
/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */

require_once 'base.php';

$app->appendComponent('body', $input);
$app->on('input.keypress', $callback, ['key', 'charCode']);

return $app;
