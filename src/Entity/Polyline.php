<?php

namespace Maris\Symfony\Geo\Entity;

use Exception;
use Maris\Symfony\Geo\Interfaces\DistanceCalculatorInterface;
use Maris\Symfony\Geo\Service\Haversine;

/**
 * Ломаная линия состоящая из двух и более точек.
 *
 * Итерируемый объект, при переборке циклом foreach перебирает внутренние точки линии.
 *
 * Функция json_encode() всегда возвращает свойство 'geometry'
 * GeoJson спецификации RFC 7946 представление географической точки.
 */
class Polyline extends Geometry
{
    /**
     * Возвращает длину линии в метрах.
     * @param DistanceCalculatorInterface $calculator
     * @return float
     * @throws Exception
     */
    public function getDistance( DistanceCalculatorInterface $calculator = new Haversine() ):float
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
    /**
     * @inheritDoc
     * @return array{type:string, coordinates:float[][] }
     */
    public function jsonSerialize(): array
    {
        return [
            "type" => "LineString",
            "bbox" => $this->bounds,
            "coordinates" => $this->coordinates->map(function (Location $coordinate):array{
                return [$coordinate->getLongitude(),$coordinate->getLatitude()];
            })
        ];
    }
}