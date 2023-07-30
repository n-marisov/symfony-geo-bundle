<?php

namespace Maris\Symfony\Geo\Entity;

use ArrayAccess;
use Countable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use IteratorAggregate;
use JsonSerializable;
use Maris\Symfony\Geo\Interfaces\LocationAggregateInterface;
use Maris\Symfony\Geo\Iterators\LocationsIterator;
use Maris\Symfony\Geo\Service\GeoCalculator;
use ReflectionException;
use SplObserver;
use SplSubject;
use TypeError;

/***
 * Сущность геометрической фигуры.
 *
 * Прослушивает событие изменения координат для обновления Geometry::$bounds.
 *
 * Функция json_encode() всегда возвращает свойство 'geometry'
 * GeoJson спецификации RFC 7946 представление географической точки.
 * @template T as list<Location>
 */
abstract class Geometry implements IteratorAggregate, Countable, ArrayAccess, JsonSerializable, SplObserver
{
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
     * Массив точек определяющих фигуру.
     * @var Collection<Location>
     */
    protected Collection $coordinates;

    /***
     * Ограничивающая рамка
     * @var Bounds|null
     */
    protected ?Bounds $bounds = null;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Bounds
     */
    public function getBounds(): Bounds
    {
        return $this->bounds ?? $this->bounds = Bounds::createFromGeometry( $this );
    }

    /**
     * Вызывается при прослушивании объекта координат.
     * @internal
     * @inheritDoc
     */
    public function update(SplSubject $subject): void
    {
        $this->bounds = null;
    }

    /**
     * Добавляет одну координату в конец фигуры.
     * @param Location $location
     * @return $this
     */
    public function add( Location $location ):self
    {
        $location->attach( $this );
        $this->coordinates->add( $location );
        $location->getGeometries()->add($this);
        $this->bounds = null;
        return $this;
    }

    /**
     * Добавляет одну или несколько координат в конец фигуры.
     * @param Location ...$locations
     * @return $this
     */
    public function push( Location ...$locations ):static
    {
        foreach ($locations as $location)
            $this->add($location);
        return $this;
    }

    /***
     * Добавляет один или несколько координат в начало геометрии.
     * @param Location ...$locations
     * @return $this
     */
    public function unshift( Location ...$locations ):static
    {
        foreach ([ ...$locations, ... $this->coordinates->toArray() ] as $position => $location)
            $this->set( $position, $location );
        return $this;
    }

    /**
     * Возвращает координату по позиции.
     * @param int $key
     * @return Location|null
     */
    public function get(int $key):?Location
    {
        return $this->coordinates->get($key);
    }

    /**
     * Устанавливает координату по ключу.
     * @param int|null $key
     * @param Location $value
     * @return $this
     */
    public function set( int|null $key, Location $value ):static
    {
        $this->bounds = null;

        if(is_null($key) || !$this->coordinates->offsetExists($key))
            $this->add($value);

        else $this->coordinates->get($key)
            ->setLatitude( $value->getLatitude() )
            ->setLongitude( $value->getLongitude() );

        return $this;
    }

    /**
     * Удаляет элемент из коллекции по ключу или значению.
     * Возвращает элемент удаленный из фигуры.
     * @param int|Location $location
     * @return Location|null
     */
    public function remove( int|Location $location ):?Location
    {
        $removed = is_int( $location )
            ? $this->coordinates->remove( $location )
            : $this->coordinates->removeElement( $location );

        /**@var Location $removed **/
        $removed->detach( $this );

        return $removed;
    }

    /**
     * Возвращает итератор для переборки координат в цикле.
     * @return LocationsIterator
     */
    public function getIterator(): LocationsIterator
    {
        return  new LocationsIterator( $this->coordinates );
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
     * Возвращает количество точек в фигуре
     * @return int
     */
    public function count(): int
    {
        return $this->coordinates->count();
    }

    /**
     * @param int $offset
     * @return bool
     */
     public function offsetExists(mixed $offset): bool
     {
         return $this->coordinates->offsetExists($offset);
     }

    /**
     * @param int $offset
     * @return Location|null
     */
     public function offsetGet(mixed $offset): ?Location
     {
         return $this->get( $offset );
     }

    /**
     * @param int $offset
     * @param Location|LocationAggregateInterface $value
     * @return void
     */
     public function offsetSet( mixed $offset, mixed $value ): void
     {
         # Получаем координаты если передан координата-имеющий объект
         if(is_a($value,LocationAggregateInterface::class))
             $value = $value->getLocation();

         # Не позволяем установить не координаты
         if(!is_a($value,Location::class))
             throw new TypeError("Недопустимое значение для объекта ".static::class);

         # Ключ должен быть целым числом
         if(!is_null($offset) && (!is_numeric($offset) || (int)$offset != $offset) )
             throw new TypeError("Недопустимый ключ для объекта ".static::class);

         $this->set( $offset, $value );
     }

    /**
     * @internal
     * @param int $offset
     * @return void
     */
     public function offsetUnset( mixed $offset ): void
     {
         $this->coordinates->offsetUnset($offset);
         foreach ($this->coordinates->getValues() as $position => $location )
            $this->set( $position, $location );
     }

    /***
     * Приводит объект к массиву.
     * @return array
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