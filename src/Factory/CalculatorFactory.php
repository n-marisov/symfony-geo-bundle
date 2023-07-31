<?php

namespace Maris\Symfony\Geo\Factory;

use Maris\Symfony\Geo\Calculator\EllipsoidalCalculator;
use Maris\Symfony\Geo\Calculator\GeoCalculator;

class CalculatorFactory
{
    public static function createCalculator( ... $args ):GeoCalculator
    {
        dump($args);
        return new EllipsoidalCalculator();
    }
}