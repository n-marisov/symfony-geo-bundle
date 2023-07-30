<?php

namespace Maris\Symfony\Geo\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Maris\Symfony\Geo\Entity\Geometry;

#[AsEntityListener(event: 'preFlush',method: 'preFlush',entity: Geometry::class)]
#[AsEntityListener(event: 'postLoad',method: 'load',entity: Geometry::class)]
class GeometryEventListener
{

    public function load( Geometry $geometry, PostLoadEventArgs $args ):void
    {

    }

    public function preFlush( Geometry $geometry, PreFlushEventArgs $args ):void
    {
        # Пересчитываем границы перед сохранением
        $geometry->getBounds();
    }

}