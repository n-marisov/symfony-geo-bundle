<?php

namespace Maris\Symfony\Geo\Factory;

use JsonException;
use Maris\Symfony\Geo\Encoder\PolylineEncoder;
use Maris\Symfony\Geo\Entity\Location;
use Maris\Symfony\Geo\Entity\Polyline;

/**
 * Фабрика для создания объекта Polyline
 */
class PolylineFactory
{
    protected PolylineEncoder $encoder;

    protected LocationFactory $locationFactory;

    /**
     * @param LocationFactory $locationFactory
     * @param PolylineEncoder $encoder
     */
    public function __construct( LocationFactory $locationFactory, PolylineEncoder $encoder )
    {
        $this->locationFactory = $locationFactory;
        $this->encoder = $encoder;
    }


    /**
     * Создает объект Location из строки (или массива json)
     * или строки координат в любом формате.
     * @param string|array $coordinates
     * @return Location|null
     */
    public function create( string|array $coordinates ):?Polyline
    {
        if(is_array($coordinates))
            return $this->createOfArray( $coordinates );

        try {
            $json = json_decode( $coordinates,true, flags: JSON_THROW_ON_ERROR );
            if(is_array($json))
                return $this->createOfArray( $json );
            return $this->encoder->decode( $coordinates );
        } catch ( JsonException ) {
            return $this->encoder->decode( $coordinates );
        }
    }

    /**
     * Создает объект координаты из массива
     * @param array $coordinates
     * @return $this|null
     */
    protected function createOfArray( array $coordinates ):?Polyline
    {
        if(array_is_list($coordinates)){
            foreach ($coordinates as $key => $value)
                $coordinates[$key] = $this->locationFactory->create($value);
        }
        return (count($coordinates) < 2) ? null : new Polyline( ...$coordinates );
    }
}