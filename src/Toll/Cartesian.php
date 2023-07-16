<?php

namespace Maris\Symfony\Geo\Toll;

/**
 * Декартова система координат.
 */
class Cartesian
{
    public float $x;
    public float $y;
    public float $z;


    /**
     * @param float $x
     * @param float $y
     * @param float $z
     */
    public function __construct( float $x, float $y, float $z )
    {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
    }
}