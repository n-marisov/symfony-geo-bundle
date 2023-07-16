<?php

namespace Maris\Symfony\Geo\Entity;


use Exception;
use Maris\Symfony\Geo\Interfaces\DistanceCalculatorInterface;
use Maris\Symfony\Geo\Service\Haversine;

class Polygon extends Geometry
{
    /**
     * Вырез в полигоне
     * @var Polygon|null
     */
    protected ?Polygon $exclude;

    /**
     * Возвращает периметр полигона в метрах.
     * @param DistanceCalculatorInterface $calculator
     * @return float
     * @throws Exception
     */
    public function getPerimeter( DistanceCalculatorInterface $calculator = new Haversine() ):float
    {
        $distance = 0.0;
        $start = null;
        /***@var Location $location **/
        foreach ( $this->getIterator() as $location ){
            if(!empty($start))
                $distance += $calculator->getDistance($start,$location);
            $start = $location;
        }
        return $distance;
    }

    /***
     * @param Polygon $polygon
     * @param array $data
     * @return array
     */
    protected static function createPolygonArray( self $polygon, array &$data = [] ):array
    {
        if( isset($polygon->coordinates) )
            $data[] = $polygon->coordinates;

        if(isset($polygon->exclude))
            static::createPolygonArray($polygon->exclude,$data);

        return $data;
    }

    /**
     * @return array{type:string, coordinates:float[][] }
     */
    public function jsonSerialize(): array
    {
        return [
            "type" => "Polygon",
            "bbox" =>$this->bounds,
            "coordinates" => static::createPolygonArray($this)
        ];
    }
}