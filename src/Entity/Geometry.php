<?php

namespace Maris\Symfony\Geo\Entity;



use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Exception;
use Maris\Symfony\Geo\Interfaces\GeometryInterface;
use Maris\Symfony\Geo\Toll\Circle;
use Traversable;

/***
 * Сущность геометрической фигуры.
 *
 *
 * Функция json_encode() всегда возвращает свойство 'geometry'
 * GeoJson спецификации RFC 7946 представление географической точки.
 */
abstract class Geometry implements GeometryInterface
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

    /***
     * Круг описанный вокруг текущей фигуры.
     * @var Circle|null
     */
    protected ?Circle $circle = null;

    /**
     *
     */
    public function __construct()
    {
        $this->coordinates = new ArrayCollection();
        $this->bounds = Bounds::create( $this );
        $this->circle = Circle::create( $this );
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
        return $this->bounds ?? $this->bounds = Bounds::create( $this );
    }

    /**
     * @return Circle
     */
    public function getCircle(): Circle
    {
        return $this->circle ?? $this->circle = Circle::create( $this );
    }



    public function addLocation( Location $location ):self
    {
        $this->coordinates->add($location);
        $location->setGeometry( $this );
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
        $coordinates = $this->coordinates->toArray();
        // Сортируем по ключу position
        usort( $coordinates ,fn(Location $a, Location $b) => $a->getPosition() <=> $b->getPosition() );

        /**@var Location $coordinate */
        foreach ($coordinates as $position => $coordinate)
            $coordinate->setPosition( $position );

        $this->coordinates = new ArrayCollection( $coordinates );

        /*** Обновляем параметры Bound ***/
        $this->bounds->calculate( $this );
    }


    /**
     * @inheritDoc
     */
    abstract public function jsonSerialize(): array;
}