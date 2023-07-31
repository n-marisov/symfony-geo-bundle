<?php

namespace Maris\Symfony\Geo\Factory;

use Maris\Symfony\Geo\Calculator\EllipsoidalCalculator;
use Maris\Symfony\Geo\Calculator\GeoCalculator;

class CalculatorFactory
{
    public static function createCalculator():GeoCalculator
    {
        return new EllipsoidalCalculator();
    }
}