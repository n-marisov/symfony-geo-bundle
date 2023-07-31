<?php

namespace Maris\Symfony\Geo\Entity;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use Maris\Symfony\Geo\Calculator\GeoCalculator;
use Maris\Symfony\Geo\Traits\GeometryListTrait;
use Maris\Symfony\Geo\Traits\PolylineJsonSerializableTrait;

/**
 * Ломаная линия состоящая из двух и более точек.
 * Не может быть меньше двух точек
 * Итерируемый объект, при переборке циклом foreach перебирает внутренние точки линии.
 *
 * Функция json_encode() всегда возвращает свойство 'geometry'
 * GeoJson спецификации RFC 7946 представление географической точки.
 */
class Polyline extends Geometry implements IteratorAggregate, ArrayAccess, Countable
{
    /**
     * @param Location $location1
     * @param Location $location2
     * @param Location ...$locations
     */
    public function __construct( Location $location1, Location $location2, Location ...$locations )
    {
        parent::__construct( $location1, $location2, ...$locations );
    }

    /***
     * Трейт поддерживает доступ к элементам коллекции точек фигуры.
     */
    use GeometryListTrait, PolylineJsonSerializableTrait;


    /**
     * Возвращает длину линии в метрах.
     * @param GeoCalculator $calculator
     * @return float
     */
    public function getDistance( GeoCalculator $calculator ):float
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
     * Создает линию из первой и последней точки полилинии.
     * @return Line
     */
    public function toLine():Line
    {
        return new Line( $this->coordinates->first(), $this->coordinates->last() );
    }
}