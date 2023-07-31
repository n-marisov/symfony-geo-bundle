<?php

namespace Maris\Symfony\Geo\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;

#[AsDoctrineListener(event: 'loadClassMetadata')]
class EntityUpdateListener
{
    public function __invoke( LoadClassMetadataEventArgs $args ):void
    {
        dump($args);
    }

}