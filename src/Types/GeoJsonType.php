<?php

namespace Maris\Symfony\Geo\Types;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\JsonType;
use Maris\Symfony\Geo\Entity\Geometry;
use Maris\Symfony\Geo\Entity\Location;
use Maris\Symfony\Geo\Entity\Polygon;
use Maris\Symfony\Geo\Entity\Polyline;

/**
 * Хранит локацию или фигуру в GEO JSON
 */
class GeoJsonType extends JsonType
{

    public function getName():string
    {
        return "geo_json";
    }

    /**
     * @param Location|Geometry|null $value
     * @param AbstractPlatform $platform
     * @return string
     * @throws ConversionException
     * @throws Exception
     */
    public function convertToDatabaseValue( $value, AbstractPlatform $platform ):string
    {
        $data = [
            "type" => match (true){
                is_a( $value,Location::class ) => "Point",
                is_a( $value,Polyline::class ) => "LineString",
                is_a( $value,Polygon::class ) => "Polygon",
                default => null
            }
        ];

        if($data["type"] === "Point"){
            $data["coordinates"] = [ $value->getLongitude(), $value->getLatitude() ];
        }else
        {
            $data["coordinates"] = [];
            /***@var Location $item */
            foreach ($value as $item)
                $data["coordinates"][] = [$item->getLongitude(),$item->getLatitude()];
        }

        return parent::convertToDatabaseValue( $data , $platform);
    }

    /**
     * @param string $value
     * @param AbstractPlatform $platform
     * @return Location|Geometry|null
     * @throws ConversionException
     * @throws Exception
     */
    public function convertToPHPValue( $value, AbstractPlatform $platform ):Location|Geometry|null
    {
        $value = parent::convertToPHPValue( $value, $platform );
        return match ( $value["type"] ?? null ){
            "Point" => $this->createLocation( $value['coordinates'] ),
            "LineString" => $this->createGeometry($value['coordinates'], new Polyline() ),
            "Polygon" => $this->createGeometry($value['coordinates'], new Polygon() ),
            default => null
        };
    }


    private function createLocation( array $coordinates ):?Location
    {
        if( !isset($coordinates[0]) || !isset($coordinates[1]) || !is_numeric($coordinates[0]) || !is_numeric($coordinates[1]))
            return null;

        return new Location( $coordinates[1], $coordinates[0] );
    }

    private function createGeometry( array $coordinates , Geometry $instance ):Geometry
    {
        foreach ($coordinates as $coordinate)
            $instance->addLocation( $this->createLocation( $coordinate ) );
        return $instance;
    }

}