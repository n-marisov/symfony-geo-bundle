<?php

namespace Maris\Symfony\Geo\Entity;

use Maris\Symfony\Geo\Calculator\GeoCalculator;

/***
 * Замкнутая фигура на карте.
 * Не может состоять менее чем из 3-х точек.
 */
class Polygon extends Geometry
{
    /***
     * @param Location $location1
     * @param Location $location2
     * @param Location $location3
     * @param Location ...$locations
     */
    public function __construct( Location $location1, Location $location2, Location $location3, Location ...$locations )
    {
        parent::__construct( $location1, $location2, $location3, ...$locations );
    }

    /**
     * Вырез в полигоне
     * @var Polygon|null
     */
    protected ?Polygon $exclude = null;

    /**
     * Возвращает периметр полигона в метрах.
     * @param GeoCalculator $calculator
     * @return float
     */
    public function getPerimeter( GeoCalculator $calculator ):float
    {
        $distance = 0.0;
        $start = null;
        /***@var Location $location **/
        foreach ( $this->getIterator() as $location ){
            if(!empty($start))
                $distance += $calculator->getDistance($start,$location);
            $start = $location;
        }

        $distance += $calculator->getDistance( $this->coordinates->first(),$this->coordinates->last() );
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

    /**
     * Определяет, входит ли точка в полигон.
     * @param Location $location
     * @return bool
     */
    public function contains( Location $location ):bool
    {
        $result = false;
        $count = $this->count();
        $latitudes = [];
        $longitudes = [];
        foreach ($this->coordinates as $item){
            $latitudes[] = $item->getLatitude();
            $longitudes[] = $item->getLongitude();
        }

        for ($i = 0, $j = $count - 1; $i < $count; $j = $i++)
            if (
                ($longitudes[$i] > $location->getLongitude()) !== ($longitudes[$j] > $location->getLongitude())
                && ($location->getLatitude() < ($latitudes[$j] - $latitudes[$i]) * ($location->getLongitude() - $longitudes[$i]) / ($longitudes[$j] - $longitudes[$i]) + $latitudes[$i])
            ) $result = !$result;

        return $result;
    }

    /**
     * Вычисляет площадь полигона.
     * @param GeoCalculator $calculator
     * @return float
     */
    public function getArea( GeoCalculator $calculator ): float
    {
        if ($this->count() <= 2) return 0.0;
        for( $i = 0,$j = 1, $area = 0;isset($this[$j]); $i=$j, $j++ )
            $area += (
                deg2rad($this[$j]->getLongitude() - $this[0]->getLongitude()) * cos(deg2rad($this[$j]->getLatitude())) * deg2rad($this[$i]->getLatitude() - $this[0]->getLatitude()) -
                deg2rad($this[$i]->getLongitude() - $this[0]->getLongitude()) * cos(deg2rad($this[$i]->getLatitude())) * deg2rad($this[$j]->getLatitude() - $this[0]->getLatitude())
            );

        return abs(0.5 * $area * $calculator->getEllipsoid()->r() ** 2 );
    }
}