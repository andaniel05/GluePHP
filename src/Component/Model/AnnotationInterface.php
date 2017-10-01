<?php

namespace PlatformPHP\GlueApps\Component\Model;

interface AnnotationInterface
{
    public function getName(): string;

    public function getAttributes(): array;

    public function getAttribute(string $attribute);
}