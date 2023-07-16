<?php

namespace Maris\Symfony\Geo\Interfaces;

use Maris\Symfony\Geo\Entity\Location;

/***
 * Калькулятор расчета расстояния.
 */
interface DistanceCalculatorInterface
{
    /**
     * Возвращает дистанцию между точками в метрах.
     * @param LocationInterface|PlaceInterface $start
     * @param LocationInterface|PlaceInterface $end
     * @return float
     */
    public function getDistance ( LocationInterface|PlaceInterface $start , LocationInterface|PlaceInterface $end ):float;
}