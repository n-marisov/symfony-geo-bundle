<?php

namespace Maris\Symfony\Geo\Traits;

use Maris\Symfony\Geo\Entity\Location;

trait PolylineJsonSerializableTrait
{
    use GeometryPropertiesTrait;

    /**
     * @return array{type:string, coordinates:float[][] }
     */
    public function jsonSerialize(): array
    {
        return [
            "type" => "LineString",
            "bbox" => $this->bounds,
            "coordinates" => $this->coordinates->map(function (Location $coordinate):array{
                return [$coordinate->getLongitude(),$coordinate->getLatitude()];
            })
        ];
    }
}