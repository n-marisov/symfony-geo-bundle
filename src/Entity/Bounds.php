<?php

namespace Maris\Symfony\Geo\Entity;

use JsonSerializable;
use Maris\Symfony\Geo\Calculator\GeoCalculator;

/**
 * Ограничивающая рамка.
 * Указывает границы любой геометрической фигуры.
 * Данные хранятся в базе как встроенный объект свойство объекта Geometry.
 */
class Bounds implements JsonSerializable
{
    /**
     * Крайняя северная координата объекта.
     * @var float|null
     */
    protected ?float $north = null;

    /**
     * Крайняя западная координата объекта.
     * @var float|null
     */
    protected ?float $west = null;

    /**
     * Крайняя южная координата объекта.
     * @var float|null
     */
    protected ?float $south = null;

    /**
     * Крайняя восточная координата объекта.
     * @var float|null
     */
    protected ?float $east = null;

    /**
     * По умолчанию границы всей планеты.
     * @param float|null $north
     * @param float|null $west
     * @param float|null $south
     * @param float|null $east
     */
    public function __construct(?float $north = 90.0, ?float $west = -180.0, ?float $south = -90.0, ?float $east = 180.0)
    {
        $this->north = $north;
        $this->west = $west;
        $this->south = $south;
        $this->east = $east;
    }

    /**
     * Устанавливает все значения на минимум
     * @return void
     */
    protected function clear():void
    {
        $this->south = 90.0;
        $this->north = -90.0;
        $this->west = 180.0;
        $this->east = -180.0;
    }


    /**
     * Вычисляет все значения геометрии
     * @param Geometry $geometry
     * @return Bounds
     */
    public function calculate( Geometry $geometry ):static
    {
        $this->clear();
        foreach ( $geometry as $location )
            $this->modify( $location );
        return $this;
    }

    /**
     * Изменяет объект таким образом, чтобы точка принадлежала объекту.
     * @param Location $location
     * @return void
     */
    public function modify( Location $location ):void
    {
        $this->south = min( $location->getLatitude(), $this->south );
        $this->west = min( $location->getLongitude(), $this->west );
        $this->north = max( $location->getLatitude(), $this->north );
        $this->east = max( $location->getLongitude(), $this->east );
    }

    /**
     * @return float|null
     */
    public function getNorth(): ?float
    {
        return $this->north;
    }

    /**
     * @return float|null
     */
    public function getWest(): ?float
    {
        return $this->west;
    }

    /**
     * @return float|null
     */
    public function getSouth(): ?float
    {
        return $this->south;
    }

    /**
     * @return float|null
     */
    public function getEast(): ?float
    {
        return $this->east;
    }

    /***
     * Сравнивает границы, без привязки к фигуре.
     * @param Bounds $bounds
     * @return bool
     */
    public function equals( Bounds $bounds ):bool
    {
        return $bounds->north == $this->south
            && $bounds->west == $this->south
            && $bounds->south == $this->south
            && $bounds->east == $this->south;
    }

    /**
     * Вычисляет, входит ли точка или фигура в границы.
     * Если передана фигура вычисляет полное вхождение фигуры в границы.
     * @param Location|Geometry $location
     * @return bool
     */
    public function contains( Location|Geometry $location ):bool
    {
        if(is_a($location,Geometry::class)){
            foreach ($location as $item)
                if(!$this->contains($item))
                    return false;
            return true;
        }
        else return $location->getLatitude() <= $this->north
            && $location->getLatitude() >= $this->south
            && $location->getLongitude() >= $this->west
            && $location->getLongitude() <= $this->east;
    }

    /**
     * Вычисляет, пресекаются ли объекты.
     * @param Bounds|Geometry $bounds
     * @return bool
     */
    public function intersect( Bounds|Geometry $bounds ):bool
    {
        if(is_a($bounds,Geometry::class))
            return $this->intersect( $bounds->getBounds() );

        return (($bounds->west >= $this->west && $bounds->west <= $this->east)
                || ($this->west >= $bounds->west && $this->west <= $bounds->east))
            && (($bounds->north <= $this->north && $bounds->north >= $this->south)
                || ($this->north <= $bounds->north && $this->north >= $bounds->south));
    }

    /**
     * Возвращает объект объединения двух границ.
     * @param Bounds $bounds
     * @return Bounds
     */
    public function union( Bounds $bounds ):Bounds
    {
        $instance = clone $this;

        $instance->modify(new Location( $bounds->north, $bounds->west ));
        $instance->modify(new Location( $bounds->south, $bounds->east ));

        return $instance;
    }

    /***
     * Вычисляет центральную точку объекта.
     * @return Location
     */
    public function getCenter():Location
    {
        $north = deg2rad( $this->north );
        $south = deg2rad( $this->south );
        $west = deg2rad( $this->west );
        $east = deg2rad( $this->east );

        $deltaX = $east - $west;

        $x = cos($south) * cos( $deltaX );
        $y = cos($south) * sin( $deltaX );

        return new Location(
            rad2deg( atan2(sin($north) + sin($south), sqrt( (cos($north)+$x)**2 + $y**2) ) ),
            rad2deg( $west + atan2($y, cos($north) + $x) )
        );
    }

    public function getNorthWest():Location
    {
        return new Location( $this->north, $this->west );
    }
    public function getSouthEast():Location
    {
        return new Location( $this->south, $this->east );
    }

    /**
     * Возвращает объект границ увеличенный на $distance.
     * Для уменьшения границ необходимо передать отрицательное число.
     * @param float $distance
     * @param GeoCalculator $calculator
     * @return Bounds
     */
    public function increase( float $distance , GeoCalculator $calculator ):Bounds
    {

        if($distance === 0.0)
            return clone $this;

        $isPositive = $distance > 0;

        $northWestBearing = $isPositive ? 315 : 135 ;
        $southEastBearing = $isPositive ? 135 : 315 ;

        return self::createFromLocations(
            $calculator->getDestination( $this->getNorthWest(), $northWestBearing, $distance ),
            $calculator->getDestination( $this->getSouthEast(), $southEastBearing, $distance )
        );
    }

    /**
     * Приводит объект к объекту bbox GeoJson.
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [$this->north, $this->west, $this->south, $this->east];
    }

    /**
     * Создает объект границ из центральной точки и длинны диагонали в метрах.
     * @param Location $center
     * @param float $diagonal
     * @param GeoCalculator $calculator
     * @return static
     */
    public static function createFromCenter( Location $center, float $diagonal, GeoCalculator $calculator ):static
    {
        $instance = new static();

        $diagonal /= 2;
        $northWest = $calculator->getDestination($center, 315, $diagonal);
        $southEast = $calculator->getDestination($center, 135, $diagonal);

        $instance->north = $northWest->getLatitude();
        $instance->west = $northWest->getLongitude();
        $instance->south = $southEast->getLatitude();
        $instance->east = $southEast->getLongitude();

        return $instance;
    }

    /**
     * Создает объект границ из геометрии
     * @param Geometry $geometry
     * @return Bounds
     */
    public static function createFromGeometry(Geometry $geometry ):Bounds
    {
        return (new static())->calculate( $geometry );
    }

    /**
     * Создает объект границ на основе двух точек.
     * Точки необязательно должны быть northWest или southEast.
     * @param Location $northWest
     * @param Location $southEast
     * @return Bounds
     */
    public static function createFromLocations( Location $northWest, Location $southEast ):Bounds
    {
        return new Bounds(
            max( $northWest->getLatitude() , $southEast->getLatitude() ),
            min( $northWest->getLongitude() , $southEast->getLongitude() ),
            min( $northWest->getLatitude() , $southEast->getLatitude() ),
            max( $northWest->getLongitude() , $southEast->getLongitude() )
        );
    }
}