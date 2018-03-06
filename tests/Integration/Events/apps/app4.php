<?php
/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */

require_once 'base.php';

$app->on('input.keypress', $callback, ['key', 'charCode']);

$app->appendComponent('body', $input);

return $app;
