<?php

namespace PlatformPHP\GlueApps\Update;

interface UpdateInterface
{
    public function getId(): string;

    public function getComponentId(): string;

    public function getData(): array;
}