<?php

namespace Maris\Symfony\Geo\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Maris\Symfony\Geo\Entity\Location;

#[AsDoctrineListener(event: 'loadClassMetadata')]
class EntityUpdateListener
{
    public function __invoke( LoadClassMetadataEventArgs $args ):void
    {
        $classMetaData = $args->getClassMetadata();

        if($classMetaData->name !== Location::class)
            return;

        $classMetaData->fieldMappings["latitude"]["precision"] = 8 + 1;
        $classMetaData->fieldMappings["latitude"]["scale"] = 6 + 1;

        $classMetaData->fieldMappings["longitude"]["precision"] = 9 + 1;
        $classMetaData->fieldMappings["longitude"]["scale"] = 6 + 1;
    }

}