<?php

namespace Maris\Symfony\Geo\DependencyInjection;

use Maris\Symfony\Geo\Entity\Geometry;
use Maris\Symfony\Geo\Toll\Ellipsoid;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class GeoExtension extends Extension implements PrependExtensionInterface
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


        # Устанавливаем эллипсоид для сервисов
        $container->setParameter("geo.calculator",$config["calculator"] ?? "spherical" );

        # Устанавливаем эллипсоид для сервисов
        $container->setParameter("geo.ellipsoid",Ellipsoid::from($config["ellipsoid"]));

        # Устанавливаем допустимую погрешность при расчетах в метрах
        $container->setParameter("geo.allowed", $config["allowed"] ?? 1.5 );

        # Устанавливаем количество знаков после запятой для кодирования полилиний
        $container->setParameter("geo.precision", $config["precision"] ?? 6 );

    }

    public function prepend(ContainerBuilder $container)
    {
        dump($container);
    }
}