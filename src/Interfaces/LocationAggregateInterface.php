<?php

namespace Maris\Symfony\Geo\Interfaces;


use Maris\Symfony\Geo\Entity\Location;

/**
 * Реализуют объекты способные хранить в себе
 * точку на карте или создавать ее.
 */
interface LocationAggregateInterface
{
    /**
     * @return Location
     */
    public function getLocation():Location;
}