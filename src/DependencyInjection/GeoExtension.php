<?php

namespace Maris\Symfony\Geo\DependencyInjection;

use Maris\Symfony\Geo\Entity\Location;
use Maris\Symfony\Geo\Service\SphericalCalculator;
use Maris\Symfony\Geo\Toll\Ellipsoid;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class GeoExtension extends Extension
{
    /**
     * Загружаем файл конфигурации
     * @inheritDoc
     */
    public function load( array $configs, ContainerBuilder $container )
    {
        $path = realpath( dirname(__DIR__).'/../Resources/config' );
        $loader = new YamlFileLoader( $container, new FileLocator( $path ) );
        $loader->load('services.yaml');

        $container->setParameter("geo.ellipsoid",Ellipsoid::WGS_84);
    }
}