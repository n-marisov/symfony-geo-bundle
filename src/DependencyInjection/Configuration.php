<?php

namespace Maris\Symfony\Geo\DependencyInjection;

use Maris\Symfony\Geo\Toll\Ellipsoid;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('geo');
        $treeBuilder->getRootNode()
            ->children()

                ->arrayNode("earth")
                    ->children()

                    # Модель земного шара по умолчанию
                    ->enumNode("model")
                        ->values(["ellipsoidal","spherical"])
                        ->defaultValue("spherical")
                    ->end()

                    # Стандарт эллипсоида.
                    ->enumNode('standard')
                        ->values(array_map(fn ( Ellipsoid $ellipsoid ) => $ellipsoid->name, Ellipsoid::cases()))
                        ->defaultValue(Ellipsoid::WGS_84->name)
                    ->end()

                    ->end()
                ->end()

                /*# Калькулятор по умолчанию
                ->enumNode("calculator")
                    ->values(["ellipsoidal","spherical"])
                    ->defaultValue("spherical")
                ->end()

                # Эллипсоид для калькулятора.
                ->enumNode('ellipsoid')
                    ->values(array_map(fn ( Ellipsoid $ellipsoid ) => $ellipsoid->name, Ellipsoid::cases()))
                    ->defaultValue(Ellipsoid::WGS_84->name)
                ->end()*/

                # Допустимая погрешность при сравнениях
                ->floatNode("allowed")->min(0.01 )->defaultValue(1.5)->end()

                # Количество знаков после запятой для PolylineEncoder
                ->integerNode("precision")->min(0)->max(PHP_FLOAT_DIG )->defaultValue(6)->end()


            ->end()
        ->end();

        return $treeBuilder;
    }
}
