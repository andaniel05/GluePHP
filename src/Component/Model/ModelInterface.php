<?php

namespace Andaniel05\GluePHP\Component\Model;

use Andaniel05\GluePHP\AbstractApp;

interface ModelInterface
{
    public function toArray(): array;

    public function getClass(): string;

    public function getAttributeList(): array;

    public function getGetter(string $attribute): ?string;

    public function getSetter(string $attribute): ?string;

    public function getExtendClassScript(): ?string;

    public function getJavaScriptClass(AbstractApp $app): ?string;
}
