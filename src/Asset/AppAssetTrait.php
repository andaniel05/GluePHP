<?php

namespace Andaniel05\GluePHP\Asset;

use Andaniel05\GluePHP\AbstractApp;

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
