<?php

namespace Maris\Symfony\Geo\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Maris\Symfony\Geo\Entity\Geometry;

#[AsEntityListener(event: 'preFlush',method: '__invoke',entity: Geometry::class)]
class GeometryEventListener
{
    public function __invoke( Geometry $geometry, PreFlushEventArgs $args ):void
    {
        # Пересчитываем границы перед сохранением
        $geometry->getBounds();
    }

}