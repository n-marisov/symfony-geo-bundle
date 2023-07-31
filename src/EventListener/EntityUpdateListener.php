<?php

namespace Maris\Symfony\Geo\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Maris\Symfony\Geo\Entity\Location;

#[AsDoctrineListener(event: 'loadClassMetadata')]
class EntityUpdateListener
{

    protected int $precision;

    /**
     * @param int $precision
     */
    public function __construct( int $precision )
    {
        $this->precision = $precision;
    }


    public function __invoke( LoadClassMetadataEventArgs $args ):void
    {
        $classMetaData = $args->getClassMetadata();

        if($classMetaData->name !== Location::class)
            return;

        $classMetaData->fieldMappings["latitude"]["precision"] = 2 + $this->precision;
        $classMetaData->fieldMappings["latitude"]["scale"] = $this->precision;

        $classMetaData->fieldMappings["longitude"]["precision"] = 3 + $this->precision;
        $classMetaData->fieldMappings["longitude"]["scale"] = $this->precision;
    }

}