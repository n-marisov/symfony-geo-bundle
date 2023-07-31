<?php

namespace Maris\Symfony\Geo\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Maris\Symfony\Geo\Entity\Bounds;
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

    /***
     * Подготавливает QueryBuilder с предустановленным
     * объектом Bounds.
     * @param Bounds $bounds
     * @param string $alias
     * @return QueryBuilder
     */
    public function createBoundsBuilder( Bounds $bounds , string $alias = "location" ):QueryBuilder
    {
        return $this->createQueryBuilder($alias)
            ->andWhere("$alias.latitude <= :north")
            ->andWhere("$alias.longitude >= :west")
            ->andWhere("$alias.latitude >= :south")
            ->andWhere("$alias.longitude <= :east")
            ->setParameter("north",$bounds->getNorth())
            ->setParameter("west",$bounds->getWest())
            ->setParameter("south",$bounds->getSouth())
            ->setParameter("east",$bounds->getEast());
    }

    /**
     * @param Bounds $bounds
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return Location[]
     */
    public function findByBounds( Bounds $bounds, ?array $orderBy = null, ?int $limit = null, ?int $offset = null ):array
    {
        $builder = $this->createBoundsBuilder($bounds);

        if( !empty($orderBy) )
            $builder->orderBy( key($orderBy) , $orderBy[]);

        if(isset($limit))
            $builder->setMaxResults( $limit );

        if(isset($offset))
            $builder->setFirstResult($offset);


        return $builder->getQuery()->getResult();
    }


}