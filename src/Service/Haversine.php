<?php

namespace Maris\Symfony\Geo\Service;

use Maris\Symfony\Geo\Interfaces\DistanceCalculatorInterface;
use Maris\Symfony\Geo\Interfaces\LocationInterface;
use Maris\Symfony\Geo\Interfaces\PlaceInterface;

/**
 * Калькулятор Хаверсайна.
 */
class Haversine implements DistanceCalculatorInterface
{
    /**
     * Радиус земли в метрах.
     * @var float
     */
    protected float $radius;

    /**
     * @param float $radius Радиус планеты в метрах.
     */
    public function __construct( float $radius = 6371008.7714 )
    {
        $this->radius = $radius;
    }


    /**
     * Возвращает дистанцию в метрах.
     * @param LocationInterface|PlaceInterface $start
     * @param LocationInterface|PlaceInterface $end
     * @return float
     */
    public function getDistance( LocationInterface|PlaceInterface $start, LocationInterface|PlaceInterface $end ): float
    {

        if(is_a($start,PlaceInterface::class))
            $start = $start->getLocation();

        if(is_a($end,PlaceInterface::class))
            $end = $end->getLocation();

        $lat1 = deg2rad( $start->getLatitude() );
        $lat2 = deg2rad( $end->getLatitude() );
        $lng1 = deg2rad( $start->getLongitude() );
        $lng2 = deg2rad( $end->getLongitude() );

        return 2 * $this->radius * asin(
                sqrt(
                    (sin(($lat2 - $lat1) / 2) ** 2)
                    + cos($lat1) * cos($lat2) * (sin(($lng2 - $lng1) / 2) ** 2)
                )
            );
    }
}