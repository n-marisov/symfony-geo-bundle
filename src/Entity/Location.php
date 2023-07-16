<?php

namespace Maris\Symfony\Geo\Entity;

use Exception;
use JsonSerializable;
use Maris\Symfony\Geo\Interfaces\LocationInterface;
use Maris\Symfony\Geo\Service\GeoCalculator;
use Maris\Symfony\Geo\Service\SphericalCalculator;
use Maris\Symfony\Geo\Toll\Cartesian;
use Maris\Symfony\Geo\Toll\Ellipsoid;
use Maris\Symfony\Geo\Toll\Orientation;
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
class Location implements LocationInterface, Stringable, JsonSerializable
{
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

    /**
     * Геометрия которой принадлежит точка
     * @var Geometry|null
     */
    protected ?Geometry $geometry = null;

    /**
     * Позиция точки в геометрии.
     * @var int|null
     */
    protected ?int $position = null;

    /**
     * Объект без широты и долготы не имеет смысла,
     * поэтому они указываются в конструкторе.
     * @param float $latitude Широта
     * @param float $longitude Долгота
     */
    public function __construct( float $latitude, float $longitude )
    {
        $this->setLatitude( $latitude )->setLongitude( $longitude );
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Geometry|null
     */
    public function getGeometry(): ?Geometry
    {
        return $this->geometry;
    }

    /**
     * @param Geometry|null $geometry
     * @return $this
     */
    public function setGeometry( ?Geometry $geometry ): self
    {
        $this->geometry = $geometry;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPosition(): ?int
    {
        return $this->position;
    }

    /**
     * @param int|null $position
     * @return $this
     */
    public function setPosition(?int $position): self
    {
        $this->position = $position;
        return $this;
    }


    /**
     * @param bool $isRadian
     * @return float
     */
    public function getLatitude( bool $isRadian = false  ): float
    {
        return ($isRadian)? deg2rad( $this->latitude ) : $this->latitude;
    }

    /**
     * @param bool $isRadian
     * @return float
     */
    public function getLongitude( bool $isRadian = false ): float
    {
        return ($isRadian)? deg2rad( $this->longitude ) : $this->longitude;
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
        return $this;
    }

    /**
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
        return join(",",[ $this->latitude, $this->longitude ]);
    }
    /**
     * Возвращает true если текущая и переданная координата указывает на одну точку на карте.
     * @param Location $location
     * @return bool
     */
    public function equals( Location $location ):bool
    {
        return $this->latitude === $location->latitude && $this->longitude === $location->longitude;
    }

    /**
     * Указывает что точки находятся на расстоянии не дальше $allowed.
     * @param Location $location
     * @param float $allowed
     * @param GeoCalculator $calculator
     * @return bool
     */
    public function sameLocation( Location $location, float $allowed = 0.001, GeoCalculator $calculator = new SphericalCalculator() ):bool
    {
        return $calculator->getDistance($this,$location) <= $allowed;
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
     * Возвращает дистанцию по перпендикуляру к вектору образованному переданными точками.
     * @param Location $lineStart
     * @param Location $lineEnd
     * @param Ellipsoid $ellipsoid
     * @return float
     */
    public function getPerpendicularDistance(  Location $lineStart, Location $lineEnd ,Ellipsoid $ellipsoid ):float
    {
        $radius = $ellipsoid->r();

        $cartesianFactory = function ( Location $location , float $radius ):Cartesian
        {
            $latitude = deg2rad( 90 - $location->getLatitude() );
            $longitude = $location->getLongitude();
            $longitude = deg2rad( ($longitude > 0) ? $longitude : $longitude + 360 );

            return new Cartesian(
                $radius * cos( $longitude ) * sin( $latitude ),
                $radius * sin( $longitude ) * sin( $latitude ),
                $radius * cos( $latitude )
            );
        };

        $point = $cartesianFactory( $this, $radius );
        $lineStart = $cartesianFactory( $lineStart, $radius );
        $lineEnd = $cartesianFactory( $lineEnd, $radius );

        $normalize = new Cartesian(
            $lineStart->y * $lineEnd->z - $lineStart->z * $lineEnd->y,
            $lineStart->z * $lineEnd->x - $lineStart->x * $lineEnd->z,
            $lineStart->x * $lineEnd->y - $lineStart->y * $lineEnd->x
        );


        $length = sqrt($normalize->x ** 2 + $normalize->y ** 2 + $normalize->z ** 2 );

        if ($length == 0.0) return 0;

        $normalize->x /= $length;
        $normalize->y /= $length;
        $normalize->z /= $length;

        $theta = $normalize->x * $point->x + $normalize->y * $point->y + $normalize->z * $point->z;

        $length = sqrt($point->x ** 2 + $point->y ** 2 + $point->z ** 2 );

        $theta /= $length;

        $distance = abs((M_PI / 2) - acos($theta));

        return $distance * $radius;
    }

    /**
     * Создает координату из строки.
     * Распознает координаты вида:
     *  "52 12.345, 13 23.456","52° 12.345, 13° 23.456", "52° 12.345′, 13° 23.456′", "52 12.345 N, 13 23.456 E","N52° 12.345′ E13° 23.456′"
     *  "52 12.345, 13 23.456","52° 12.345, 13° 23.456", "52° 12.345′, 13° 23.456′", "52 12.345 N, 13 23.456 E","N52° 12.345′ E13° 23.456′"
     *  "65.5, 44.755544" или "46.42552 37.976"
     *  "40.2S, 135.3485W" или "56.234°N, 157.245°W"
     * @param string $location
     * @return static|null
     */
    public static function fromString( string $location ):?static
    {
        /**
         * Объединяем минуты и секунды.
         */
        $location = preg_replace_callback(
            '/(\d+)(°|\s)\s*(\d+)(\'|′|\s)(\s*([0-9.]*))("|\'\'|″|′′)?/u',
             function (array $matches): string {
                return sprintf('%d %f', $matches[1], (float)$matches[3] + (float)$matches[6] / 60);
            },
            $location
        );


        # "52 12.345, 13 23.456","52° 12.345, 13° 23.456", "52° 12.345′, 13° 23.456′", "52 12.345 N, 13 23.456 E","N52° 12.345′ E13° 23.456′"
        if (preg_match("/(-?\d{1,2})°?\s+(\d{1,2}\.?\d*)['′]?[, ]\s*(-?\d{1,3})°?\s+(\d{1,2}\.?\d*)['′]?/u", $location, $match) === 1) {
            $latitude  = (int)$match[1] >= 0
                ? (int)$match[1] + (float)$match[2] / 60
                : (int)$match[1] - (float)$match[2] / 60;
            $longitude = (int)$match[3] >= 0
                ? (int)$match[3] + (float)$match[4] / 60
                : (int)$match[3] - (float)$match[4] / 60;

            return (is_numeric($latitude) && is_numeric($longitude))
                ? new static((float)$latitude, (float)$longitude)
                : null;
        }

        # "52 12.345, 13 23.456","52° 12.345, 13° 23.456", "52° 12.345′, 13° 23.456′", "52 12.345 N, 13 23.456 E","N52° 12.345′ E13° 23.456′"
        elseif (preg_match("/([NS]?\s*)(\d{1,2})°?\s+(\d{1,2}\.?\d*)['′]?(\s*[NS]?)[, ]\s*([EW]?\s*)(\d{1,3})°?\s+(\d{1,2}\.?\d*)['′]?(\s*[EW]?)/ui", $location, $match) === 1) {
            $latitude = (int)$match[2] + (float)$match[3] / 60;
            if (strtoupper(trim($match[1])) === 'S' || strtoupper(trim($match[4])) === 'S') {
                $latitude = - $latitude;
            }
            $longitude = (int)$match[6] + (float)$match[7] / 60;
            if (strtoupper(trim($match[5])) === 'W' || strtoupper(trim($match[8])) === 'W') {
                $longitude = - $longitude;
            }
            return (is_numeric($latitude) && is_numeric($longitude))
                ? new static((float)$latitude, (float)$longitude)
                : null;
        }
         # "65.5, 44.755544" или "46.42552 37.976"
        elseif (preg_match('/(-?\d{1,2}\.?\d*)°?[, ]\s*(-?\d{1,3}\.?\d*)°?/u', $location, $match) === 1) {

            return (is_numeric($match[1]) && is_numeric($match[2]))
                ? new static((float)$match[1], (float)$match[2])
                : null;
        }

        #"40.2S, 135.3485W" или "56.234°N, 157.245°W"
        elseif (preg_match("/([NS]?\s*)(\d{1,2}\.?\d*)°?(\s*[NS]?)[, ]\s*([EW]?\s*)(\d{1,3}\.?\d*)°?(\s*[EW]?)/ui", $location, $match) === 1) {
            $latitude = $match[2];
            if (strtoupper(trim($match[1])) === 'S' || strtoupper(trim($match[3])) === 'S') {
                $latitude = - $latitude;
            }
            $longitude = $match[5];
            if (strtoupper(trim($match[4])) === 'W' || strtoupper(trim($match[6])) === 'W') {
                $longitude = - $longitude;
            }

            return (is_numeric($latitude) && is_numeric($longitude))
                ? new static((float)$latitude, (float)$longitude)
                : null;
        }
        return null;
    }

    /**
     * Создает объект координаты из массива
     * @param float[]|array{string:float} $location
     * @return $this|null
     */
    public static function fromJson( array $location ):?static
    {
        foreach ( $location as $key => $value )
            if(in_array($key,[1,"lat","latitude"]) && is_numeric($value))
                $latitude = (float) $value;
            elseif(in_array($key,[0,"lon","long","longitude"]) && is_numeric($value))
                $longitude = (float) $value;

        if(isset($latitude) && isset($longitude))
            return new static($latitude, $longitude);

        return null;
    }
}