<?php

namespace Maris\Symfony\Geo\Interfaces;

use Doctrine\Common\Collections\Collection;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/**
 * Характеризует собой любую фигуру на карте
 */
interface GeometryInterface extends IteratorAggregate, JsonSerializable
{
    /**
     * Возвращает id сущности
     * @return int|null
     */
    public function getId():?int;

    /**
     * Перебирает все точки фигуры
     * @return Traversable
     */
    public function getIterator(): Traversable;

    /**
     * Возвращает объект "geometry" GeoJson.
     * @return array{type:string, coordinates:float[][] }
     */
    public function jsonSerialize(): array;
}