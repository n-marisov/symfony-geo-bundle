<?php

namespace Maris\Symfony\Geo\Service;

use Generator;
use Maris\Symfony\Geo\Entity\Location;
use Maris\Symfony\Geo\Entity\Polyline;
use RuntimeException;

/***
 * Кодирует и декодирует полилинию в строку
 *  с помощью алгоритма кодирования координат Google.
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
        if( $precision < 0 || $precision > PHP_FLOAT_DIG )
            throw new RuntimeException(
                "Недопустимое значение \$precision , разрешено от 0 <= precision <= ".PHP_FLOAT_DIG." ."
            );
        $this->precision = $precision;
    }

    /**
     * @return int
     */
    public function getPrecision(): int
    {
        return $this->precision;
    }



    /**
     * Декодирует строку координат в объект полилинии.
     * @param string $encoded
     * @return Polyline
     */
    public function decode( string $encoded ):Polyline
    {
        for ( $i = 0, $j = 0,$pvs = [0,0],$f = []; $j < strlen($encoded); $i++ ){

            $s = $r = 0x00;
            do {
                $bit = ord(substr($encoded, $j++)) - 63;
                $r |= ( $bit & 0x1f ) << $s;
                $s += 5;
            } while ( $bit >= 0x20 );

            $pvs[$i % 2] = $pvs[$i % 2] + ( ($r & 1) ? ~($r >> 1) : ($r >> 1) );

            if( $i % 2 === 1)
                $f[] = new Location(
                    $pvs[0] * ( 1 / pow(10, $this->precision ) ),
                    $pvs[1] * ( 1 / pow(10, $this->precision ) ),
                );
        }

        return new Polyline( ...$f );
    }

    /**
     * Кодирует полилинию в строку.
     * Если передан второй параметр, то он будет дополнен
     * созданной полилинией вместо создания новой строки.
     * @param Polyline $polyline
     * @param string $encoded
     * @return string
     */
    public function encode( Polyline $polyline , string $encoded = ""):string
    {
        $previous = [0,0];
        foreach ( $this->locationGenerator( $polyline ) as $position => $number )
            $encoded .= $this->encodeNumber( $position, $number, $previous );
        return $encoded;
    }

    /***
     * Кодирует одно значение координаты.
     * @param int $i
     * @param float $number
     * @param array $previous
     * @return string
     */
    protected function encodeNumber(int $i, float $number , array &$previous ):string
    {
        $number = (int) round($number * pow(10, $this->precision ) );
        $diff = $number - $previous[$i % 2];
        $previous[$i % 2] = $number;
        return $this->encodeChunk( ($diff < 0) ? ~($diff << 1) : ($diff << 1) );
    }

    /**
     * Кодирует число в строку.
     * @param float $number
     * @param string $chunk
     * @return string
     */
    protected function encodeChunk( float $number, string $chunk = "" ):string
    {
        while ( $number >= 0x20 ) {
            $chunk .= chr((0x20 | ($number & 0x1f)) + 63);
            $number >>= 5;
        }
        return $chunk . chr($number + 63);
    }

    /**
     * Генератор для последовательной переборки значений координат.
     * Позволяет не копировать в память массив с всеми значениями
     * координат полилинии
     * @param Polyline $polyline
     * @return Generator
     */
    protected function locationGenerator( Polyline $polyline ): Generator
    {
        foreach ($polyline as $item){
            yield $item->getLatitude();
            yield $item->getLongitude();
        }
    }

}