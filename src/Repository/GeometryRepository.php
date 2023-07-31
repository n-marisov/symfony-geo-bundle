<?php

namespace Maris\Symfony\Geo\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Maris\Symfony\Geo\Entity\Bounds;
use Maris\Symfony\Geo\Entity\Geometry;
use Maris\Symfony\Geo\Entity\Location;

/***
 * Класс репозиторий сущности Geometry.
 *
 *
 */
class GeometryRepository extends ServiceEntityRepository
{
    public function __construct( ManagerRegistry $registry )
    {
        parent::__construct( $registry, Geometry::class );
    }

    /**
     * Сохраняет сущность.
     * @param Geometry $location
     * @param bool $flush
     * @return void
     */
    public function save( Geometry $location , bool $flush = false ):void
    {
        $this->getEntityManager()->persist($location);
        if($flush)
            $this->getEntityManager()->flush();
    }

    /**
     * Удаляет сущность.
     * @param Geometry $location
     * @param bool $flush
     * @return void
     */
    public function remove( Geometry $location , bool $flush = false ):void
    {
        $this->getEntityManager()->remove($location);
        if($flush)
            $this->getEntityManager()->flush();
    }

    public function findByBounds( Bounds $bounds, array $criteria = [], ?array $orderBy = null, $limit = null, $offset = null )
    {
        /*$criteria["bounds.north"] = min( $criteria["bounds.north"] ?? 90.0, $bounds->getNorth() );
        $criteria["bounds.west"] = max( $criteria["bounds.west"] ?? -180.0, $bounds->getWest() );
        $criteria["bounds.south"] = max($criteria["bounds.south"] ?? -180.0, $bounds->getSouth() );
        $criteria["bounds.east"] = min( $criteria["bounds.south"] ?? -180.0, $bounds->getEast() );
        return parent::findBy($criteria, $orderBy, $limit, $offset);*/
    }


}