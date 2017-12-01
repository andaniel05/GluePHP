<?php

require_once 'base.php';

use Andaniel05\GluePHP\Tests\Integration\Entities\Components\VueButton;

$text = $_GET['text'];
$app->button1->setText($text);

return $app;
