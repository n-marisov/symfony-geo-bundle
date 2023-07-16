<?php

namespace Maris\Symfony\Geo\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Maris\Symfony\Geo\Entity\Location;

/***
 * Класс репозиторий сущности Location.
 *
 *
 */
class LocationRepository extends ServiceEntityRepository
{
    public function __construct( ManagerRegistry $registry )
    {
        parent::__construct( $registry, Location::class );
    }

    /**
     * Сохраняет сущность.
     * @param Location $location
     * @param bool $flush
     * @return void
     */
    public function save(Location $location , bool $flush = false ):void
    {
        $this->getEntityManager()->persist($location);
        if($flush)
            $this->getEntityManager()->flush();
    }

    /**
     * Удаляет сущность.
     * @param Location $location
     * @param bool $flush
     * @return void
     */
    public function remove(Location $location , bool $flush = false ):void
    {
        $this->getEntityManager()->remove($location);
        if($flush)
            $this->getEntityManager()->flush();
    }


}