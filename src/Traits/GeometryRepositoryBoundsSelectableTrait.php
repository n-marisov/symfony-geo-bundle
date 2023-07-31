<?php

namespace Maris\Symfony\Geo\Traits;

use Doctrine\ORM\QueryBuilder;
use Maris\Symfony\Geo\Entity\Bounds;

/**
 * Трейт для добавления возможности выборки фигур
 * по объекту границ.
 */
trait GeometryRepositoryBoundsSelectableTrait
{

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

        if( !empty($orderBy) && is_string( $key = key($orderBy)) )
            $builder->orderBy( $key , $orderBy[$key] );

        if(isset($limit))
            $builder->setMaxResults( $limit );

        if(isset($offset))
            $builder->setFirstResult($offset);


        return $builder->getQuery()->getResult();
    }
}