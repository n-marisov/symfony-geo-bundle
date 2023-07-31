<?php

namespace Maris\Symfony\Geo\Entity;

use ArrayAccess;
use Countable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use IteratorAggregate;
use JsonSerializable;
use Maris\Symfony\Geo\Calculator\GeoCalculator;
use Maris\Symfony\Geo\Interfaces\LocationAggregateInterface;
use Maris\Symfony\Geo\Iterators\LocationsIterator;
use Maris\Symfony\Geo\Traits\GeometryPropertiesTrait;
use ReflectionException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use TypeError;

/***
 * Сущность геометрической фигуры.
 *
 * Прослушивает событие изменения координат для обновления Geometry::$bounds.
 *
 * Функция json_encode() всегда возвращает свойство 'geometry'
 * GeoJson спецификации RFC 7946 представление географической точки.
 */
abstract class Geometry implements JsonSerializable
{

    use GeometryPropertiesTrait;

    /**
     * Создает объект геометрии
     */
    public function __construct( Location ... $locations )
    {
        $this->coordinates = new ArrayCollection();
        foreach ($locations as $location)
            $this->coordinates->add( $location );
    }

    /**
     * ID в базе данных
     * @var int|null
     */
    protected ?int $id = null;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /***
     * Упрощает текущую геометрию.
     * @param GeoCalculator $calculator
     * @param float|null $distance
     * @param float|null $bearing
     * @return $this
     * @throws ReflectionException
     */
    public function simplify( GeoCalculator $calculator , ?float $distance = null, ?float $bearing = null ):static
    {
        $instance = $calculator->simplify( $this, $distance, $bearing);
        $this->coordinates = $instance->coordinates;
        $this->bounds = null;
        return $this;
    }

    /***
     * Приводит объект к массиву.
     * @return array<Location>
     */
     public function toArray():array
     {
         return $this->coordinates->toArray();
     }

    /**
     * @inheritDoc
     */
    abstract public function jsonSerialize(): array;
 }