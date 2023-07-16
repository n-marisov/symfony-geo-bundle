<?php

namespace Maris\Symfony\Geo;

use Maris\Symfony\Geo\DependencyInjection\GeoExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class GeoBundle extends AbstractBundle{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new GeoExtension();
    }

}