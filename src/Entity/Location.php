<?php

namespace Maris\Symfony\Geo\Entity;

use Doctrine\Common\Collections\Collection;
use Exception;
use JsonSerializable;
use Maris\Symfony\Geo\Service\GeoCalculator;
use Maris\Symfony\Geo\Toll\Orientation;
use SplObjectStorage;
use SplObserver;
use SplSubject;
use Stringable;

/**
 * Сущность географической точки.
 *
 * Хранит значения в базе данных с точностью до шести знаков после запятой.
 *
 * При установке значения Широты ($latitude) вычисляется точное значение координаты,
 * таким образом всегда выполняется тождество -90 <= $latitude <= 90.
 *
 * При установке значения Долгота ($longitude) вычисляется точное значение координаты,
 * удаляется лишние круги, таким образом всегда выполняется тождество -180 <= $longitude <= 180.
 *
 * Функция json_encode() всегда возвращает свойство 'geometry'
 * GeoJson спецификации RFC 7946 представление географической точки.
 */
final class Location implements Stringable, JsonSerializable, SplSubject
{
    /**
     * Объект без широты и долготы не имеет смысла,
     * поэтому они указываются в конструкторе.
     * Значения приводятся к допустимому диапазону.
     * @param float $latitude Широта
     * @param float $longitude Долгота
     */
    public function __construct( float $latitude, float $longitude )
    {
        $this->storage = new SplObjectStorage();
        $this->setLatitude( $latitude )->setLongitude( $longitude );
    }

    /**
     * ID в базе данных
     * @var int|null
     */
    protected ?int $id = null;

    /**
     * Значение на оси Y
     * Допустимый диапазон значений: -90.0 .. +90.0
     * @var float
     */
    private float $latitude;

    /**
     * Значение на оси X
     * Допустимый диапазон значений: -180.0 .. +180.0
     * @var float
     */
    private float $longitude;

    /***
     * Связанные фигуры.
     * @var Collection
     */
    private Collection $geometries;

    /**
     * @var SplObjectStorage|null
     */
    private ?SplObjectStorage $storage = null;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return float
     */
    public function getLatitude(): float
    {
        return $this->latitude;
    }

    /**
     * @return float
     */
    public function getLongitude(  ): float
    {
        return $this->longitude;
    }

    /**
     * Устанавливает свойство latitude (широта).
     * Нормализует значение в диапазон -90.0 .. 90.0.
     * @param float $latitude
     * @return $this
     */
    public function setLatitude( float $latitude ): self
    {
        if ($latitude >= 360)
            $latitude = fmod($latitude, 360);

        if ($latitude >= 180 || $latitude <= -180)
            $latitude = 0 - fmod($latitude, 180);

        if ($latitude > 90)
            $latitude = 90 - fmod($latitude, 90);
        elseif ($latitude < -90)
            $latitude = -90.0 - fmod($latitude, 90.0);

        $this->latitude = $latitude;
        $this->notify();
        return $this;
    }

    /**
     * Устанавливает свойство longitude (долгота).
     * Нормализует значение в диапазон -180.0 .. 180.0.
     * @param float $longitude
     * @return $this
     */
    public function setLongitude( float $longitude ): self
    {
        if ($longitude >= 360.0 || $longitude <= -360.0)
            $longitude = fmod($longitude, 360.0);

        if ($longitude > 180.0)
            $longitude = -180.0 + fmod($longitude, 180.0);
        elseif ($longitude < -180.0)
            $longitude = 180.0 + fmod($longitude, 180.0);

        $this->longitude = $longitude;
        $this->notify();
        return $this;
    }

    /**
     * Возвращает true если текущая и переданная координата указывает на одну точку на карте.
     * Если передан калькулятор, то вычисляется приближенное расстояние на основании допустимой
     * погрешности калькулятора.
     * @param Location $location
     * @param GeoCalculator|null $calculator
     * @return bool
     */
    public function equals( Location $location , ?GeoCalculator $calculator = null ):bool
    {
        if(isset($calculator))
            return  $calculator->isAllowed( $calculator->getDistance($this,$location) );

        return $this->latitude === $location->latitude && $this->longitude === $location->longitude;
    }


    /**
     * Определяет в какую сторону текущая точка разводит вектор из двух переданных точек.
     * @param Location $lineStart Начало вектора.
     * @param Location $lineEnd Направление вектора.
     * @return Orientation
     */
    public function getOrientation( Location $lineStart, Location $lineEnd ):Orientation
    {
        return Orientation::fromFloat(
            (($lineEnd->latitude - $lineStart->latitude) * ($this->longitude - $lineEnd->longitude))
            - (($lineEnd->longitude - $lineStart->longitude) * ($this->latitude - $lineEnd->latitude))
        );
    }

    /***
     * Возвращает дистанцию по перпендикуляру к прямой образованной переданными точками.
     * @param Location $lineStart
     * @param Location $lineEnd
     * @param GeoCalculator $calculator
     * @return float
     */
    public function getPerpendicularDistance(  Location $lineStart, Location $lineEnd , GeoCalculator $calculator ):float
    {
        return $calculator->getPerpendicularDistance( $lineStart, $lineEnd, $this );
    }


    /**
     * Возвращает значение ключа 'geometry' GeoJson.
     * @throws Exception
     */
    public function jsonSerialize():array
    {
        return [
            "type" => "Point",
            "coordinates"=>[ $this->longitude, $this->latitude ]
        ];
    }

    /**
     * Приводит объект к строке.
     * @return string
     */
    public function __toString():string
    {
        return implode(",",[ $this->latitude, $this->longitude ]);
    }

    /**
     * @internal
     * @inheritDoc
     */
    public function attach(SplObserver $observer): void
    {
        $this->storage->attach( $observer );
    }

    /**
     * @internal
     * @inheritDoc
     */
    public function detach(SplObserver $observer): void
    {
        $this->storage->detach( $observer );
    }

    /**
     * @internal
     * @inheritDoc
     */
    public function notify(): void
    {
        /**@var SplObserver $observer */
        foreach ($this->storage as $observer)
            $observer->update( $this );
    }
}