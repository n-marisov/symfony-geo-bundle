<?php

namespace Maris\Symfony\Geo\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Maris\Symfony\Geo\Entity\Bounds;
use Maris\Symfony\Geo\Entity\Geometry;
use Maris\Symfony\Geo\Entity\Location;

/***
 * Класс репозиторий сущности Geometry.
 * @extends ServiceEntityRepository<Geometry>
 *
 * @method Geometry|null find($id, $lockMode = null, $lockVersion = null)
 * @method Geometry|null findOneBy(array $criteria, array $orderBy = null)
 * @method Geometry[]    findAll()
 * @method Geometry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
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


    public function createBoundsBuilder( Bounds $bounds , string $alias = "geometry" ):QueryBuilder
    {
        return $this->createQueryBuilder("geometry")
            ->andWhere("$alias.bounds.north <= :north")
            ->andWhere("$alias.bounds.west >= :west")
            ->andWhere("$alias.bounds.south >= :south")
            ->andWhere("$alias.bounds.east <= :east")
            ->setParameter("north",$bounds->getNorth())
            ->setParameter("west",$bounds->getWest())
            ->setParameter("south",$bounds->getSouth())
            ->setParameter("east",$bounds->getEast());
    }


    public function findByBounds( Bounds $bounds, ?array $orderBy = null, $limit = null, $offset = null ):array
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