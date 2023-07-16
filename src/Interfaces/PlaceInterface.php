<?php

namespace Maris\Symfony\Geo\Interfaces;

use Maris\Symfony\Geo\Entity\Location;

/**
 * Интерфейс точки интереса.
 */
interface PlaceInterface
{
    /**
     * Возвращает географические координаты точки.
     * @return Location
     */
    public function getLocation():Location;
}