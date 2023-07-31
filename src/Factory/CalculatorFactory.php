<?php

namespace Maris\Symfony\Geo\Factory;

use http\Encoding\Stream;
use Maris\Symfony\Geo\Calculator\EllipsoidalCalculator;
use Maris\Symfony\Geo\Calculator\GeoCalculator;

class CalculatorFactory
{
    /**
     * @var class-string
     */
    protected string $calculatorClass;

    /**
     * @param string $calculatorClass
     */
    public function __construct( string $calculatorClass )
    {
        $this->calculatorClass = $calculatorClass;
    }

    public function __invoke():GeoCalculator
    {
        return new $this->calculatorClass();
    }


    public static function createCalculator( ... $args ):GeoCalculator
    {
        dump($args);
        return new EllipsoidalCalculator();
    }
}