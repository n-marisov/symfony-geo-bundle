<?php

namespace Maris\Symfony\Geo\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Maris\Symfony\Geo\Entity\Geometry;

/***
 * Обработчик жизненного цикла объектов Geometry::class.
 */
#[AsEntityListener(event: 'preFlush',method: 'preFlush',entity: Geometry::class)]
class GeometryEventListener
{
    /***
     * Событие перед сохранением фигуры.
     * @param Geometry $geometry
     * @param PreFlushEventArgs $args
     * @return void
     */
    public function preFlush( Geometry $geometry, PreFlushEventArgs $args ):void
    {
        # Пересчитываем границы перед сохранением
        $geometry->getBounds();
    }

}