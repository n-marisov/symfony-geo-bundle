<?php

namespace Maris\Symfony\Geo\Service;

use Maris\Symfony\Geo\Entity\Location;
use Maris\Symfony\Geo\Entity\Polyline;

/***
 * Фабрика полилиний
 */

class PolylineEncoder
{

    /***
     * Точность количество знаков после запятой в координатах.
     * @var int
     */
    protected int $precision;

    /**
     * @param int $precision
     */
    public function __construct( int $precision )
    {
        $this->precision = $precision;
    }

    /**
     * Декодирует строку координат в объект полилинии
     * @param string $encodedString
     * @return Polyline
     */
    public function decode( string $encodedString ):Polyline
    {
        $points = array();
        $index = $i = 0;
        $previous = array(0,0);
        while ($i < strlen($encodedString)) {
            $shift = $result = 0x00;
            do {
                $bit = ord(substr($encodedString, $i++)) - 63;
                $result |= ($bit & 0x1f) << $shift;
                $shift += 5;
            } while ($bit >= 0x20);

            $diff = ($result & 1) ? ~($result >> 1) : ($result >> 1);
            $number = $previous[$index % 2] + $diff;
            $previous[$index % 2] = $number;
            $index++;
            $points[] = $number * ( 1 / pow(10, $this->precision ) );
        }

        $polyline = new Polyline();
        foreach ( array_chunk($points, 2) as $point )
            if( count($point) == 2 )
                $polyline->addLocation(new Location($point[0],$point[1]));

        return $polyline;
    }

    /**
     * Кодирует полилинию в строку
     * @param Polyline $polyline
     * @return string
     */
    public function encode( Polyline $polyline ):string
    {
        $result = '';
        $index = 0;
        $previous = array(0,0);
        $points = [];

        /***@var Location $location **/
        foreach ( $polyline as $location ){
            $points[] = round( $location->getLatitude() ,$this->precision );
            $points[] = round( $location->getLongitude(),$this->precision );
        }

        foreach ( $points as $number ) {
            $number = (float)($number);
            $number = (int)round($number * pow(10, $this->precision ));
            $diff = $number - $previous[$index % 2];
            $previous[$index % 2] = $number;
            $number = $diff;
            $index++;
            $number = ($number < 0) ? ~($number << 1) : ($number << 1);
            $chunk = '';
            while ( $number >= 0x20 ) {
                $chunk .= chr((0x20 | ($number & 0x1f)) + 63);
                $number >>= 5;
            }
            $chunk .= chr($number + 63);
            $result .= $chunk;
        }
        return $result;
    }
}