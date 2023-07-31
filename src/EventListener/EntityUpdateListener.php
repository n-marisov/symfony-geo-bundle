<?php

namespace Maris\Symfony\Geo\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Maris\Symfony\Geo\Entity\Bounds;
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
        $meta = $args->getClassMetadata();

        if($meta->name === Location::class){

            $meta->fieldMappings["latitude"]["precision"] = 2 + $this->precision;
            $meta->fieldMappings["latitude"]["scale"] = $this->precision;

            $meta->fieldMappings["longitude"]["precision"] = 3 + $this->precision;
            $meta->fieldMappings["longitude"]["scale"] = $this->precision;
        }

        elseif ($meta->name === Bounds::class){

            $meta->fieldMappings["north"]["precision"] = 2 + $this->precision;
            $meta->fieldMappings["north"]["scale"] = $this->precision;

            $meta->fieldMappings["west"]["precision"] = 3 + $this->precision;
            $meta->fieldMappings["west"]["scale"] = $this->precision;

            $meta->fieldMappings["south"]["precision"] = 2 + $this->precision;
            $meta->fieldMappings["south"]["scale"] = $this->precision;

            $meta->fieldMappings["east"]["precision"] = 3 + $this->precision;
            $meta->fieldMappings["east"]["scale"] = $this->precision;
        }

    }

}