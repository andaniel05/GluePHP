<?php
/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */

require_once 'base.php';

$input->on('keypress', $callback, ['key', 'charCode']);

$app->appendComponent('body', $input);

return $app;
