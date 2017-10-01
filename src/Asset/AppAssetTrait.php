<?php

namespace PlatformPHP\GlueApps\Asset;

use PlatformPHP\GlueApps\AbstractApp;

trait AppAssetTrait
{
    protected $app;

    public function getApp(): ?AbstractApp
    {
        return $this->app;
    }

    public function setApp(?AbstractApp $app)
    {
        $this->app = $app;
    }
}
