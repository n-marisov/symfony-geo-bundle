<?php

namespace Maris\Symfony\Geo\Service;

use Maris\Symfony\Geo\Entity\Bounds;
use Maris\Symfony\Geo\Entity\Geometry;
use Maris\Symfony\Geo\Entity\Location;
use Maris\Symfony\Geo\Entity\Polygon;
use Maris\Symfony\Geo\Entity\Polyline;
use Maris\Symfony\Geo\Interfaces\LocationInterface;
use Maris\Symfony\Geo\Interfaces\PlaceInterface;
use Maris\Symfony\Geo\Toll\Bearing;
use Maris\Symfony\Geo\Toll\Ellipsoid;
use Maris\Symfony\Geo\Toll\Orientation;
use ReflectionClass;
use ReflectionException;

abstract class GeoCalculator
{
    /**
     * @var Ellipsoid
     */
    protected Ellipsoid $ellipsoid;

    /**
     * Вычисляет начальный азимут между точками
     * @param Location $start
     * @param Location $end
     * @return float
     */
    abstract public function getInitialBearing( Location $start , Location $end ):float;

    /**
     * Вычисляет конечный азимут между точками
     * @param Location $start
     * @param Location $end
     * @return float
     */
    abstract public function getFinalBearing( Location $start , Location $end ):float;

    /**
     * Вычисляет все азимуты между точками
     * @param Location $start
     * @param Location $end
     * @return Bearing
     */
    abstract public function getFullBearing( Location $start , Location $end ):Bearing;


    /**
     * Вычисляет точку на удалении $distance и азимуте $initialBearing от точки $location.
     * @param Location $location
     * @param float $initialBearing
     * @param float $distance
     * @return Location
     */
    abstract public function getDestination( Location $location , float $initialBearing, float $distance ):Location;

    /**
     * Возвращает дистанцию между точками в метрах.
     * @param LocationInterface|PlaceInterface $start
     * @param LocationInterface|PlaceInterface $end
     * @return float
     */
    abstract public function getDistance ( LocationInterface|PlaceInterface $start , LocationInterface|PlaceInterface $end ):float;


    /**
     * @param Ellipsoid $ellipsoid
     */
    public function __construct( Ellipsoid $ellipsoid = Ellipsoid::WGS_84 )
    {
        $this->ellipsoid = $ellipsoid;
    }

    /**
     * Преобразует в объект локации.
     * @param LocationInterface|PlaceInterface $point
     * @return LocationInterface
     */
    protected function convertPoint( LocationInterface|PlaceInterface $point ):LocationInterface
    {
        return is_a($point,PlaceInterface::class) ? $point->getLocation() : $point;
    }

    /***
     * Создает упрощенную фигуру.
     * @param Geometry $geometry
     * @param float|null $distance
     * @param float|null $angle Минимальный требуемый угол в градусах между двумя соседними сегментами полилинии
     * @return Geometry
     * @throws ReflectionException
     */
    public function simplify( Geometry $geometry, ?float $distance = null, ?float $angle = null ):Geometry
    {
        $result = $geometry->toArray();
        if(isset($distance))
            $result = $this->distanceSimplify( $result, $distance );

        if(isset($angle))
            $result = $this->angleSimplify( $result, $angle );

        $instance = (new ReflectionClass($geometry))->newInstance();
        /**@var  Location $item **/
        foreach ($result as $item)
            $instance->addLocation( $item );

        return $instance;
    }

    /**
     * Упрощает линию по перпендикулярному расстоянию.
     * Использует алгоритм Дугласа–Пекера.
     * @param array $line
     * @param float $distance
     * @return array
     */
    protected function distanceSimplify( array $line, float $distance ):array
    {
        $dMax = 0;
        $index = 0;
        $count = count( $line );
        $size = $count - 2;

        for ($i = 1; $i <= $size; $i++)
            if ( ($distance = $line[$i]->getPerpendicularDistance( $line[0], $line[$count - 1], $this->ellipsoid)) > $dMax ) {
                $index = $i;
                $dMax = $distance;
            }

        if ($dMax > $distance) {
            $lineSplitFirst = array_slice($line, 0, $index + 1);
            $lineSplitSecond = array_slice($line, $index, $count - $index);

            $resultsSplit1 = count($lineSplitFirst) > 2
                ? $this->distanceSimplify($lineSplitFirst, $distance)
                : $lineSplitFirst;

            $resultsSplit2 = count($lineSplitSecond) > 2
                ? $this->distanceSimplify($lineSplitSecond, $distance)
                : $lineSplitSecond;

            array_pop($resultsSplit1);

            return array_merge($resultsSplit1, $resultsSplit2);
        }

        return [$line[0], $line[$count - 1]];
    }

    /**
     * Упрощает линию по минимальному углу между сегментами фигуры.
     * @param array $points
     * @param float $angle
     * @return array
     */
    protected function angleSimplify(array $points, float $angle  ):array
    {
        $count = count($points);
        if($count <= 3) return  $points;
        $result = [];
        $i = 0;
        $result[] = $points[$i];
        do {
            $i++;
            if ($i === $count - 1) {
                $result[] = $points[$i];
                break;
            }

            $b1 = $this->getInitialBearing( $points[$i - 1], $points[$i] );
            $b2 = $this->getInitialBearing( $points[$i], $points[$i + 1] );

            $difference = min(fmod($b1 - $b2 + 360, 360), fmod($b2 - $b1 + 360, 360));

            if ($difference > $angle)
                $result[] = $points[$i];
        } while ($i < $count);

        return $result;
    }


    /**
     * Определяет пересечение фигур.
     * @param Location|Geometry $figure1
     * @param Location|Geometry $figure2
     * @return bool
     */
    public function intersects( Location|Geometry $figure1, Location|Geometry $figure2 ):bool
    {
        # Пересечение двух точек.
        if(is_a($figure1,Location::class) && is_a($figure2,Location::class))
            return $figure1->sameLocation( $figure2 );

        # Пересечение точки и полигона
        elseif (is_a($figure1,Polygon::class) && is_a($figure2,Location::class))
            return $figure1->contains( $figure2 );
        elseif (is_a($figure1,Location::class) && is_a($figure2,Polygon::class))
            return $figure2->contains( $figure1 );

        # Пересечение полилинии или полигона и полигона
        elseif (is_a($figure1,Polygon::class) && is_a($figure2,Polyline::class))
            return $this->intersectsPolylineOfPolygon( $figure1, $figure2 );
        elseif (is_a($figure1,Polyline::class) && is_a($figure2,Polygon::class))
            return $this->intersectsPolylineOfPolygon( $figure2, $figure1 );
        elseif (is_a($figure1,Polygon::class) && is_a($figure2,Polygon::class))
            return $this->intersectsPolylineOfPolygon( $figure1, $figure2 );

        # Пересечение двух полилиний
        elseif (is_a($figure1,Polyline::class) && is_a($figure2,Polyline::class))
            return $this->intersectsPolylineOfPolyline( $figure1, $figure2 );

        # Пересечение точки и полилинии
        elseif (is_a($figure1,Polyline::class) && is_a($figure2,Location::class))
            return $this->intersectsLocationOfPolyline( $figure2, $figure1 );
        elseif (is_a($figure1,Location::class) && is_a($figure2,Polyline::class))
            return $this->intersectsLocationOfPolyline( $figure1, $figure2 );

        return false;
    }

    /**
     * Если одна из точек $figure принадлежит $polygon
     * значит они пересекаются.
     * @param Polygon $polygon
     * @param Polyline|Polygon $figure
     * @return bool
     */
    protected function intersectsPolylineOfPolygon( Polygon $polygon, Polyline|Polygon $figure ):bool
    {
        foreach ($figure as $location)
            if($polygon->contains($location))
                return true;
        return false;
    }

    /**
     * Определяет, пересекаются ли две линии.
     * @param Polyline $p1
     * @param Polyline $p2
     * @return bool
     */
    protected function intersectsPolylineOfPolyline( Polyline $p1, Polyline $p2 ):bool
    {
        $count_1 = $p1->count();
        $count_2 = $p2->count();
        for ($i = 0,$j = 1; $j <= $count_1; $i++, $j++ )
            for ($start = 0,$end = 1; $end <= $count_2; $start++, $end++ ){
                $orientations = [
                    $p1[$i]->getOrientation($p1[$start],$p1[$end]),
                    $p1[$i]->getOrientation($p1[$start],$p1[$end]),
                    $p1[$j]->getOrientation($p1[$start],$p1[$end]),
                    $p1[$j]->getOrientation($p1[$start],$p1[$end])
                ];
                if($orientations[0] !== $orientations[1] && $orientations[2] !== $orientations[3])
                    return true;
                elseif (in_array(Orientation::COLLINEAR,$orientations)){
                    $b1 = new Bounds(
                        $p1[$i]->getLatitude(),
                        $p1[$i]->getLongitude(),
                        $p1[$j]->getLatitude(),
                        $p1[$j]->getLongitude()
                    );
                    $b2 = new Bounds(
                        $p2[$i]->getLatitude(),
                        $p2[$i]->getLongitude(),
                        $p2[$j]->getLatitude(),
                        $p2[$j]->getLongitude()
                    );
                    if($b1->intersect($b2))
                        return true;
                }
            }
        return false;
    }

    /***
     * Определяет пересечение точки и полилинии.
     * Точка принадлежит линии если входит в
     * объект границ линии и перпендикулярное расстояние
     * меньше или равно допустимой погрешности.
     * @param Location $l
     * @param Polyline $p
     * @return bool
     */
    protected function intersectsLocationOfPolyline( Location $l, Polyline $p ):bool
    {
        $count = $p->count();
        for ($i = 0,$j = 1; $j <= $count; $i++, $j++ ){
            $b = new Bounds(
                $p[$i]->getLatitude(),
                $p[$i]->getLongitude(),
                $p[$j]->getLatitude(),
                $p[$j]->getLongitude()
            );
            if($b->contains($l) && $l->getPerpendicularDistance( $p[$i], $p[$j], $this->ellipsoid ))
                return true;
        }
        return false;
    }

}