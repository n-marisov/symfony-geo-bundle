<?php

namespace Maris\Symfony\Geo\Entity;

use ArrayAccess;
use Countable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Exception;
use Maris\Symfony\Geo\Interfaces\GeometryInterface;
use Maris\Symfony\Geo\Service\GeoCalculator;
use ReflectionException;
use Traversable;

/***
 * Сущность геометрической фигуры.
 *
 *
 * Функция json_encode() всегда возвращает свойство 'geometry'
 * GeoJson спецификации RFC 7946 представление географической точки.
 */
abstract class Geometry implements GeometryInterface, Countable, ArrayAccess
{
    /**
     * ID в базе данных
     * @var int|null
     */
    protected ?int $id = null;

    /**
     * Указывает на то что объект не изменялся.
     * @var bool
     */
    protected bool $isOriginal = true;

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
     *
     */
    public function __construct( Location ... $locations )
    {
        $this->coordinates = new ArrayCollection();

        foreach ($locations as $location)
            $this->coordinates->add( $location );

        $this->bounds = Bounds::createFromGeometry( $this );
    }

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




    public function add( Location $location ):self
    {
        $this->coordinates->add( $location );
        $this->bounds->modify( $location );
        return $this;
    }

    /**
     * Возвращает итератор для переборки координат в цикле.
     * @return Traversable<int,Location>
     * @throws Exception
     */
    public function getIterator(): Traversable
    {
        return  $this->coordinates->getIterator();
    }

    /**
     * Стабилизирует данные фигуры.
     * 1. Стабилизирует порядок координат.
     * 2. Стабилизирует объект границ.
     * @return void
     */
    public function stability():void
    {
        # Если объект не изменялся, то нечего не делаем.
        if($this->isOriginal) return;

        /*** Стабилизирует порядок координат ***/
       // $coordinates = $this->coordinates->toArray();
        // Сортируем по ключу position
        //usort( $coordinates ,fn(Location $a, Location $b) => $a->getPosition() <=> $b->getPosition() );

        /**@var Location $coordinate */
        /*foreach ($coordinates as $position => $coordinate)
            $coordinate->setPosition( $position );

        $this->coordinates = new ArrayCollection( $coordinates );*/

        /*** Обновляем параметры Bound ***/
        $this->bounds->calculate( $this );
    }


    /**
     * @inheritDoc
     */
    abstract public function jsonSerialize(): array;

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
        $this->bounds->calculate($this);
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
         return $this->coordinates->offsetGet( $offset );
     }

    /**
     * @param int $offset
     * @param Location $value
     * @return void
     */
     public function offsetSet( mixed $offset, mixed $value ): void
     {
         # Не позволяем установить не координаты
         if(!is_a($value,Location::class))
             return;

         # Если ключ null добавляем в конец
         if(is_null($offset)){
             $this->add( $value );
             return;
         }

         # Ключ должен быть целым числом
         if(!is_numeric($offset) || (int)$offset != $offset)
             return;

         # Добавляем в конец списка
         if( $offset >=  $this->coordinates->count() )
         {
             $this->add( $value );
             return;
         }
         $this->coordinates[$offset] = $value;


         /*for( $i = $offset, $prewiev = clone $this[$offset]; $i < $this->count(); $i++ ){
            $this->coordinates[$i]
                ->setLatitude( $value->getLatitude() )
                ->setLongitude( $value->getLongitude() );
         }*/
     }

    /**
     * @internal
     * @param int $offset
     * @return void
     */
     public function offsetUnset( mixed $offset ): void
     {
         $this->coordinates->offsetUnset($offset);
     }

    /***
     * Приводит объект к массиву
     * @return array
     */
     public function toArray():array
     {
         return $this->coordinates->toArray();
     }
 }