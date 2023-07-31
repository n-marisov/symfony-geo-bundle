<?php

namespace Maris\Symfony\Geo\Factory;

use Maris\Symfony\Geo\Calculator\EllipsoidalCalculator;
use Maris\Symfony\Geo\Calculator\GeoCalculator;
use Maris\Symfony\Geo\Calculator\SphericalCalculator;
use Maris\Symfony\Geo\Toll\Ellipsoid;

class CalculatorFactory
{
    /**
     * @var class-string
     */
    protected string $calculator;

    protected Ellipsoid $ellipsoid;
    protected float $allowed;

    /**
     * @param string $calculator
     * @param Ellipsoid $ellipsoid
     * @param float $allowed
     */
    public function __construct( string $calculator , Ellipsoid $ellipsoid, float $allowed )
    {
        $this->calculator = $calculator;
        $this->ellipsoid = $ellipsoid;
        $this->allowed = $allowed;
    }

    public function __invoke():GeoCalculator
    {
        return match ( $this->calculator ){
            "ellipsoidal" => new EllipsoidalCalculator( $this->ellipsoid, $this->allowed ),
            default =>new SphericalCalculator( $this->ellipsoid, $this->allowed )
        };
    }

}