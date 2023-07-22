<?php

namespace Maris\Symfony\Geo\Toll;

use Maris\Symfony\Geo\Entity\Location;

/***
 * Луч на карте.
 * Определяется точкой из которой исходит луч и азимутом
 * определяющим направление луча.
 */
class Beam
{
    /**
     * Точка из которой выходит луч.
     * @var Location
     */
    protected Location $location;

    /**
     * Начальный азимут, направление луча.
     * @var float
     */
    protected float $bearing;

    /**
     * @param Location $location
     * @param float $bearing
     */
    public function __construct( Location $location, float $bearing )
    {
        $this->location = $location;
        $this->bearing = $bearing;
    }


}