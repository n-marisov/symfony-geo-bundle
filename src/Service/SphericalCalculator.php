<?php

namespace Maris\Symfony\Geo\Service;

use Maris\Symfony\Geo\Entity\Location;
use Maris\Symfony\Geo\Interfaces\LocationAggregateInterface as LocationAggregate;
use Maris\Symfony\Geo\Toll\Bearing;

/**
 * Калькулятор сферической земли.
 */
class SphericalCalculator extends GeoCalculator
{
    /**
     * Возвращает дистанцию в метрах.
     * Использует алгоритм Хаверсайна.
     * @param Location|LocationAggregate $start
     * @param Location|LocationAggregate $end
     * @return float
     */
    public function getDistance( Location|LocationAggregate $start, Location|LocationAggregate $end ): float
    {
        $start = $this->pointToLocation( $start );
        $end = $this->pointToLocation( $end );

        $lat1 = deg2rad( $start->getLatitude() );
        $lat2 = deg2rad( $end->getLatitude() );
        $lng1 = deg2rad( $start->getLongitude() );
        $lng2 = deg2rad( $end->getLongitude() );

        return 2 * $this->ellipsoid->r() * asin(
                sqrt(
                    (sin(($lat2 - $lat1) / 2) ** 2)
                    + cos($lat1) * cos($lat2) * (sin(($lng2 - $lng1) / 2) ** 2)
                )
            );
    }

    public function getInitialBearing( Location|LocationAggregate $start,Location|LocationAggregate $end ): float
    {

        $start = $this->pointToLocation( $start );
        $end = $this->pointToLocation( $end );

        $lat1 = deg2rad($start->getLatitude());
        $lat2 = deg2rad($end->getLatitude());
        $lng1 = deg2rad($start->getLongitude());
        $lng2 = deg2rad($end->getLongitude());

        $y = sin($lng2 - $lng1) * cos($lat2);
        $x = cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($lng2 - $lng1);

        $bearing = rad2deg(atan2($y, $x));

        if ($bearing < 0) {
            $bearing = fmod($bearing + 360, 360);
        }

        return $bearing;
    }

    public function getFinalBearing( Location|LocationAggregate $start, Location|LocationAggregate $end ): float
    {
        $start = $this->pointToLocation( $start );
        $end = $this->pointToLocation( $end );

        return fmod($this->getInitialBearing( $end, $start ) + 180, 360);
    }

    public function getFullBearing( Location|LocationAggregate $start, Location|LocationAggregate $end ): Bearing
    {
        $start = $this->pointToLocation( $start );
        $end = $this->pointToLocation( $end );

        return (new Bearing())
            ->setInitial( $this->getInitialBearing( $start, $end ) )
            ->setFinal( $this->getFinalBearing( $start, $end ) );
    }

    /**
     * @inheritDoc
     */
    public function getDestination( Location|LocationAggregate $location, float $initialBearing, float $distance ): Location
    {

        $location = $this->pointToLocation( $location );

        $D = $distance / $this->ellipsoid->r();
        $B = deg2rad( $initialBearing );
        $lat = deg2rad($location->getLatitude());
        $long = deg2rad($location->getLongitude());

        return new Location(
            rad2deg( asin(sin($lat) * cos($D) + cos($lat) * sin($D) * cos($B)) ),
            rad2deg($long + atan2(sin($B) * sin($D) * cos($lat), cos($D) - sin($lat) * sin($lat)) )
        );
    }
}