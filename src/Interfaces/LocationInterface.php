<?php

namespace Maris\Symfony\Geo\Interfaces;

/***
 * Интерфейс определяет любую точку на карте.
 */
interface LocationInterface
{
    /**
     * Возвращает географическую Широту
     * @return float
     */
    public function getLatitude():float;

    /**
     * Возвращает географическую долготу.
     * @return float
     */
    public function getLongitude():float;
}