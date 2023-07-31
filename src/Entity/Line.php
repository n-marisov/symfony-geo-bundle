<?php

namespace Maris\Symfony\Geo\Entity;

use Maris\Symfony\Geo\Calculator\GeoCalculator;
use Maris\Symfony\Geo\Toll\Bearing;
use Maris\Symfony\Geo\Toll\Orientation;
use Maris\Symfony\Geo\Traits\PolylineJsonSerializableTrait;

/**
 * Линия состоящая из двух точек.
 *
 * Не является итерируемыми.
 */
class Line extends Geometry
{
    public function __construct( Location $start, Location $end )
    {
        parent::__construct( $start, $end );
    }


    use PolylineJsonSerializableTrait;


    public function getStart():Location
    {
        return $this->coordinates->first();
    }

    public function getEnd():Location
    {
        return $this->coordinates->last();
    }


    public function setStart( Location $location ):self
    {
        $this->coordinates->set( 0, $location );
        return $this;
    }

    public function setEnd( Location $location ):self
    {
        $this->coordinates->set( 1, $location );
        return $this;
    }

    /**
     * Возвращает длину линии в метрах.
     * @param GeoCalculator $calculator
     * @return float
     */
    public function getDistance( GeoCalculator $calculator ):float
    {
        return $calculator->getDistance( $this->getStart(), $this->getEnd() );
    }

    /**
     * Вычисляет азимуты.
     * @param GeoCalculator $calculator
     * @return Bearing
     */
    public function getBearing( GeoCalculator $calculator ):Bearing
    {
        return $calculator->getFullBearing( $this->getStart(),$this->getEnd() );
    }

    /**
     * Возвращает развернутую линию
     * @return $this
     */
    public function reverse():static
    {
        return new static( $this->getEnd(), $this->getStart() );
    }

    /***
     * Определяет в какую сторону точка разводит
     * текущую линию.
     * @param Location $location
     * @return Orientation
     */
    public function getOrientation( Location $location ):Orientation
    {
        return Orientation::fromFloat(
            (($this->getEnd()->getLatitude() - $this->getStart()->getLatitude()) * ($location->getLongitude() - $this->getEnd()->getLongitude()))
            - (($this->getEnd()->getLongitude() - $this->getStart()->getLongitude()) * ($location->getLatitude() - $this->getEnd()->getLatitude()))
        );
    }

    /**
     * Создает полилинию из линии.
     * @return Polyline
     */
    public function toPolyline():Polyline
    {
        return new Polyline( $this->getStart(),$this->getEnd() );
    }
}