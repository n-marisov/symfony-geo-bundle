<?php

namespace Maris\Symfony\Geo\Traits;

use ArrayAccess;
use Countable;
use Doctrine\Common\Collections\Collection;
use Maris\Symfony\Geo\Entity\Bounds;
use Maris\Symfony\Geo\Entity\Location;
use Maris\Symfony\Geo\Interfaces\LocationAggregateInterface;
use Maris\Symfony\Geo\Iterators\LocationsIterator;
use TypeError;

/**
 * Трейт поддерживает доступ к элементам коллекции как к массиву.
 * @implements ArrayAccess
 * @implements Countable
 */
trait GeometryListTrait
{

    use GeometryPropertiesTrait;

    /**
     * Добавляет одну координату в конец фигуры.
     * @param Location $location
     * @return $this
     */
    public function add( Location $location ):static
    {
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
        $removed->getGeometries()->removeElement( $this );

        return $removed;
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
     * Возвращает количество точек в фигуре
     * @return int
     */
    public function count(): int
    {
        return $this->coordinates->count();
    }

    /**
     * Возвращает итератор для переборки координат в цикле.
     * @return LocationsIterator
     */
    public function getIterator(): LocationsIterator
    {
        return  new LocationsIterator( $this->coordinates );
    }

}