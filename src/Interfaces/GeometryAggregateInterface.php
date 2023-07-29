<?php

namespace Maris\Symfony\Geo\Interfaces;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Maris\Symfony\Geo\Entity\Geometry;

/**
 * Характеризует собой любую фигуру на карте
 */
interface GeometryAggregateInterface extends IteratorAggregate, Countable, ArrayAccess, JsonSerializable
{
    /**
     * Возвращает фигуру связанную с объектом.
     * @return Geometry
     */
    public function getGeometry():Geometry;
}