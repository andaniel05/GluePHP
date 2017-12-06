<?php

namespace Andaniel05\GluePHP\Update;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
interface UpdateInterface
{
    public function getId(): string;

    public function getComponentId(): string;

    public function getData(): array;
}
