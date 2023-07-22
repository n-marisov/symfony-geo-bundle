<?php

namespace Maris\Symfony\Geo\Factory;

use JsonException;
use Maris\Symfony\Geo\Entity\Location;

/**
 * Фабрика для создания объекта координат
 */
class LocationFactory
{
    /**
     * Создает объект Location из строки (или массива json)
     * или строки координат в любом формате.
     * @param string|array $data
     * @return Location|null
     */
    public function create( string|array $data ):?Location
    {
        if(is_array($data))
            return $this->createOfArray( $data );

        try {
            $json = json_decode( $data,true, flags: JSON_THROW_ON_ERROR );
            if(is_array($json))
                return $this->createOfArray( $json );
            return $this->createOfString( $data );
        } catch ( JsonException ) {
            return $this->createOfString( $data );
        }
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
    protected function createOfString( string $location ):?Location
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
                ? new Location((float)$latitude, (float)$longitude)
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
                ? new Location( (float)$match[1], (float)$match[2] )
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
                ? new Location( (float)$latitude, (float)$longitude )
                : null;
        }
        return null;
    }

    /**
     * Создает объект координаты из массива
     * @param float[]|array{string:float} $location
     * @return $this|null
     */
    protected function createOfArray( array $location ):?Location
    {
        foreach ( $location as $key => $value )
            if(in_array($key,[1,"lat","latitude"]) && is_numeric($value))
                $latitude = (float) $value;
            elseif(in_array($key,[0,"lon","long","longitude"]) && is_numeric($value))
                $longitude = (float) $value;

        if(isset($latitude) && isset($longitude))
            return new Location( $latitude, $longitude );

        return null;
    }
}