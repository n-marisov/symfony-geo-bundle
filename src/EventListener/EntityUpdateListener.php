<?php

namespace Maris\Symfony\Geo\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Maris\Symfony\Geo\Entity\Bounds;
use Maris\Symfony\Geo\Entity\Location;

/**
 * Обновляет Mapping исходя из настроек.
 */
#[AsDoctrineListener(event: 'loadClassMetadata')]
class EntityUpdateListener
{
    /***
     * Количество знаков после запятой для значений координат.
     * @var int
     */
    protected int $precision;

    /**
     * @param int $precision
     */
    public function __construct( int $precision )
    {
        $this->precision = $precision;
    }

    /**
     * Главный метод события.
     * @param LoadClassMetadataEventArgs $args
     * @return void
     */
    public function __invoke( LoadClassMetadataEventArgs $args ):void
    {
        $meta = $args->getClassMetadata();

        if($meta->name === Location::class)
            $this->updateLocation( $meta );

        elseif ($meta->name === Bounds::class)
           $this->updateBounds( $meta );


    }

    /**
     * Обновляет сущность Location::class.
     * @param ClassMetadata $meta
     * @return void
     */
    private function updateLocation( ClassMetadata $meta ):void
    {
        $meta->fieldMappings["latitude"]["precision"] = 2 + $this->precision;
        $meta->fieldMappings["latitude"]["scale"] = $this->precision;

        $meta->fieldMappings["longitude"]["precision"] = 3 + $this->precision;
        $meta->fieldMappings["longitude"]["scale"] = $this->precision;
    }

    /**
     * Обновляет сущность Bounds::class.
     * @param ClassMetadata $meta
     * @return void
     */
    private function updateBounds( ClassMetadata $meta ):void
    {
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