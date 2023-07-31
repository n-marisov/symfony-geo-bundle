<?php

namespace Maris\Symfony\Geo\Traits;

use Doctrine\Common\Collections\Collection;
use Maris\Symfony\Geo\Entity\Bounds;
use Maris\Symfony\Geo\Entity\Location;

/**
 * Трейт содержит главные свойства фигуры
 * и методы для взаимодействия с ними.
 */
trait GeometryPropertiesTrait
{
    /**
     * Массив точек определяющих фигуру.
     * @var Collection<Location>
     */
    protected Collection $coordinates;

    /***
     * Ограничивающая рамка
     * @var Bounds|null
     */
    protected ?Bounds $bounds = null;

    /**
     * @return Bounds
     */
    public function getBounds(): Bounds
    {
        return $this->bounds ?? $this->bounds = Bounds::createFromGeometry( $this );
    }

    /**
     * Сбрасывает объект границ фигуры.
     * @return void
     */
    public function clearBounds():void
    {
        $this->bounds = null;
    }


}