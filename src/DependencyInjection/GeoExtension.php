<?php

namespace Maris\Symfony\Geo\DependencyInjection;

use Maris\Symfony\Geo\Entity\Geometry;
use Maris\Symfony\Geo\Toll\Ellipsoid;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class GeoExtension extends Extension
{
    /**
     * Загружаем файл конфигурации
     * @inheritDoc
     */
    public function load( array $configs, ContainerBuilder $container )
    {

        $configuration = new Configuration();

        $config = $this->processConfiguration( $configuration, $configs );

        $path = realpath( dirname(__DIR__).'/../Resources/config' );
        $loader = new YamlFileLoader( $container, new FileLocator( $path ) );
        $loader->load('services.yaml');


        $earth = $config["earth"] ?? [];
        $accuracy = $config["accuracy"] ?? [];

        # Устанавливаем эллипсоид для сервисов
        $container->setParameter("geo.earth.model",$earth["model"] ?? "spherical" );

        # Устанавливаем эллипсоид для сервисов
        $container->setParameter("geo.earth.standard",Ellipsoid::from($earth["standard"]));

        # Устанавливаем допустимую погрешность при сравнениях в метрах
        $container->setParameter("geo.accuracy.allowed", $accuracy["allowed"] ?? 1.5 );

        # Устанавливаем количество знаков после запятой для кодирования полилиний
        $container->setParameter("geo.accuracy.precision", $accuracy["precision"] ?? 6 );

    }
}