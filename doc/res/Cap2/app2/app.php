<?php

/////////////////
// Composición //
/////////////////

// Instancia la app. El primer argumento especifica la ruta del controlador
// de procesamiento.
$app = new App('process.php');

// Instancia los componentes. El primer argumento especifica el identificador del componente.
$input = new Input('input');
$label = new Label('label');
$button = new Button('button');

// Inserta los componentes en la sección 'body' de la app.
$app->appendComponent('body', $input);
$app->appendComponent('body', $label);
$app->appendComponent('body', $button);

////////////////////////////
// Declaración de eventos //
////////////////////////////

// Declara y define en el mismo lugar el evento 'click' del botón.
$button->on('click', function () use ($input, $label) {
    $label->setText('Hola ' . $input->getText());
});

return $app;
